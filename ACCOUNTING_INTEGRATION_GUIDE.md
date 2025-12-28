# Accounting System Integration Guide

## Overview

This document explains the relationship between GL Accounts, Cash Boxes, and Chart of Accounts in the accounting system, and how they work together following professional accounting principles.

## Key Components

### 1. GL Accounts (`gl_accounts` table)
**Purpose**: Reference accounts used for categorizing transactions in petty cash and other modules.

**Location**: `/finance/settings`

**Characteristics**:
- Simple structure: code, name, category, is_active
- Used as reference/quick-select accounts
- **NOT** part of the accounting structure itself
- Automatically synced to Chart of Accounts

**Relationship**:
- Links to `chart_of_accounts` via `chart_of_account_id`
- When created, automatically creates a corresponding Chart of Account
- Multiple GL Accounts can reference the same Chart of Account

### 2. Cash Boxes (`cash_boxes` table)
**Purpose**: Physical cash containers used for petty cash management.

**Location**: `/finance/settings`

**Characteristics**:
- Represents actual cash boxes/safes
- Has currency and current balance
- **NOT** accounting accounts themselves
- Automatically synced to Chart of Accounts as Asset accounts

**Relationship**:
- Links to `chart_of_accounts` via `chart_of_account_id`
- When created, automatically creates a Chart of Account of type "Asset" (Current Asset)
- Multiple Cash Boxes can reference the same Chart of Account

### 3. Chart of Accounts (`chart_of_accounts` table)
**Purpose**: The official accounting structure used in General Ledger and Journal Entries.

**Location**: `/modules/accounting/chart-of-accounts`

**Characteristics**:
- Full accounting structure with hierarchy (parent-child relationships)
- Has types: Asset, Liability, Equity, Income, Expense
- Has categories, opening balances, descriptions
- Used in General Ledger entries
- Used in Journal Entries
- Tracks balances and transactions

**Relationship**:
- Can be linked from GL Accounts (via `gl_accounts.chart_of_account_id`)
- Can be linked from Cash Boxes (via `cash_boxes.chart_of_account_id`)
- One Chart of Account can be referenced by multiple GL Accounts or Cash Boxes

## How They Work Together

### Double-Entry Bookkeeping Principle

When a petty cash transaction occurs:

1. **GL Account** (from finance/settings) is selected for categorization
   - Example: "Office Expenses" (GL Account code: 5000)

2. **Cash Box** (from finance/settings) is selected for payment source
   - Example: "Main Cash Box" (Cash Box)

3. **System automatically uses Chart of Accounts**:
   - GL Account "Office Expenses" → Chart of Account (Expense type)
   - Cash Box "Main Cash Box" → Chart of Account (Asset type, Current Asset)

4. **General Ledger entries are created**:
   - **Debit**: Expense Account (increases expense)
   - **Credit**: Cash Account (decreases cash asset)

This follows the double-entry bookkeeping principle where every transaction affects at least two accounts.

## Auto-Sync Mechanism

### When GL Account is Created:
1. System checks if Chart of Account exists with same code
2. If not, creates new Chart of Account based on GL Account category:
   - Maps category to type (Assets → Asset, Expense → Expense, etc.)
   - Maps category to enum category
   - Sets is_active status
3. Links GL Account to Chart of Account via `chart_of_account_id`

### When Cash Box is Created:
1. System checks if Chart of Account exists for this cash box
2. If not, creates new Chart of Account:
   - Type: Asset
   - Category: Current Asset
   - Code: CASH-{CURRENCY}-{NAME}
   - Name: Cash - {Cash Box Name}
   - Opening Balance: Cash Box current_balance
3. Links Cash Box to Chart of Account via `chart_of_account_id`

### Sync All Feature
- Button in finance/settings to sync all existing GL Accounts and Cash Boxes
- Useful for migrating existing data
- Only syncs accounts that aren't already linked

## Professional Accounting Standards

### 1. Separation of Concerns
- **GL Accounts**: Quick reference for transaction categorization
- **Chart of Accounts**: Official accounting structure
- **Cash Boxes**: Physical cash management

### 2. Single Source of Truth
- Chart of Accounts is the single source of truth for accounting
- GL Accounts and Cash Boxes are references that link to Chart of Accounts
- All General Ledger entries use Chart of Accounts, not GL Accounts

### 3. Audit Trail
- All Chart of Accounts have created_by and updated_by
- All General Ledger entries track source (petty_cash, journal_entry, etc.)
- Relationships are maintained for traceability

### 4. Data Integrity
- Foreign key constraints ensure referential integrity
- Cascade rules prevent orphaned records
- Validation ensures proper account types and categories

## Usage Examples

### Example 1: Creating a New Expense Account
1. Go to `/finance/settings`
2. Create GL Account:
   - Code: 6000
   - Name: Travel Expenses
   - Category: Expense
3. System automatically:
   - Creates Chart of Account (type: Expense, category: Operating Expense)
   - Links GL Account to Chart of Account
4. GL Account now appears in petty cash dropdown
5. Chart of Account appears in Chart of Accounts page

### Example 2: Creating a New Cash Box
1. Go to `/finance/settings`
2. Create Cash Box:
   - Name: USD Cash Box
   - Currency: USD
   - Current Balance: 1000.00
3. System automatically:
   - Creates Chart of Account (type: Asset, category: Current Asset)
   - Code: CASH-USD-USD-CASH-BOX
   - Opening Balance: 1000.00
   - Links Cash Box to Chart of Account
4. Cash Box now available for petty cash transactions
5. Chart of Account appears in Chart of Accounts page

### Example 3: Petty Cash Transaction
1. User creates petty cash voucher
2. Selects GL Account: "Office Supplies" (links to Chart of Account)
3. Selects Cash Box: "Main Cash Box" (links to Chart of Account)
4. System creates General Ledger entries:
   - Debit: Office Supplies (Expense account from Chart of Accounts)
   - Credit: Main Cash Box (Asset account from Chart of Accounts)
5. Both entries reference the petty cash voucher
6. Balances are updated in Chart of Accounts

## Benefits of This Architecture

1. **User-Friendly**: GL Accounts provide simple reference for non-accountants
2. **Professional**: Chart of Accounts maintains proper accounting structure
3. **Flexible**: Can create Chart of Accounts manually or auto-sync from GL Accounts
4. **Traceable**: Clear relationships between all components
5. **Standards-Compliant**: Follows double-entry bookkeeping principles
6. **Scalable**: Can handle complex accounting hierarchies

## Migration Notes

When running the migration `2025_12_04_000001_link_gl_accounts_and_cash_boxes_to_chart_of_accounts`:

1. Adds `chart_of_account_id` to both tables
2. Creates foreign key relationships
3. Existing records will have NULL `chart_of_account_id`
4. Use "Sync All" button to link existing records
5. New records will auto-sync automatically

## Troubleshooting

### GL Account not showing in Chart of Accounts
- Check if `chart_of_account_id` is set
- Use "Sync All" button to sync
- Verify GL Account has a valid category

### Cash Box not showing in Chart of Accounts
- Check if `chart_of_account_id` is set
- Use "Sync All" button to sync
- Verify Cash Box is active

### General Ledger entries not created
- Ensure both GL Account and Cash Box are synced
- Verify Chart of Accounts exist and are active
- Check transaction source is properly set



