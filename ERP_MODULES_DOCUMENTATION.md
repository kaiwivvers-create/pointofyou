# ERP Modules Documentation

This document explains the functionality and purpose of each ERP module in the system.

## 1. Inventory Module (Manajemen Stok)

### Purpose
The Inventory module manages all product stock, categories, and stock movements. It helps track what products you have, how many are in stock, and when stock levels change.

### Components

#### Inventory Categories
- **Purpose**: Organize products into logical groups (e.g., Beverages, Food, Supplies)
- **Fields**: Name, Description
- **Function**: Helps categorize and filter products for easier management

#### Products
- **Purpose**: Store detailed information about each item in inventory
- **Fields**:
  - Name: Product name
  - SKU: Unique stock keeping unit identifier
  - Category: Which category the product belongs to
  - Purchase Price: Cost to buy the product
  - Selling Price: Price to sell the product
  - Stock Quantity: Current amount in stock
  - Min Stock Level: Alert threshold when stock is low
  - Unit: Measurement unit (pcs, kg, liter, etc.)
  - Description: Additional product details
- **Function**: Central product database with pricing and stock tracking

#### Stock Movements
- **Purpose**: Track all changes to stock levels
- **Fields**:
  - Product: Which product's stock changed
  - Type: Movement type (in = stock added, out = stock removed, adjustment = manual correction)
  - Quantity: How many units moved
  - Unit Cost: Cost per unit (for stock-in)
  - Reference: Invoice or order number
  - Notes: Additional details
- **Function**: Complete audit trail of all stock changes for accountability

### How It Works
1. Create categories to organize products
2. Add products with their details and initial stock
3. When stock changes (purchases, sales, waste), record stock movements
4. System automatically updates product stock quantity
5. View stock movements to track history and identify patterns

### Key Benefits
- Prevents stockouts by tracking minimum stock levels
- Provides complete history of stock changes
- Helps with purchasing decisions based on stock levels
- Enables accurate cost tracking with purchase prices

---

## 2. Payroll Module (Data Gaji)

### Purpose
The Payroll module manages employee information, salary calculations, attendance tracking, and payment records. It automates payroll processing and ensures accurate employee compensation.

### Components

#### Employees
- **Purpose**: Store comprehensive employee information
- **Fields**:
  - Employee ID: Unique identifier
  - Full Name: Employee's name
  - Email: Contact email
  - Phone: Contact number
  - Position: Job title/role
  - Hire Date: When they started
  - Base Salary: Monthly base salary
  - Bank Name: Bank for salary transfer
  - Bank Account: Account number
  - Address: Residential address
  - Status: active, inactive, or terminated
- **Function**: Central employee database with all necessary payroll information

#### Salaries
- **Purpose**: Calculate and track salary payments for each period
- **Fields**:
  - Employee: Who the salary is for
  - Base Salary: Fixed monthly salary
  - Overtime Rate: Additional pay for overtime hours
  - Allowance: Additional benefits (transport, meal, etc.)
  - Bonus: Performance or other bonuses
  - Deductions: Amounts to subtract (loans, advances, etc.)
  - Tax: Tax amount withheld
  - Net Salary: Final amount to pay (calculated automatically)
  - Period Start/End: Pay period dates
  - Status: pending, approved, or paid
- **Function**: Complete salary calculation with all components

#### Attendance
- **Purpose**: Track employee daily attendance and working hours
- **Fields**:
  - Employee: Who attended
  - Date: Attendance date
  - Check In: Time they arrived
  - Check Out: Time they left
  - Hours Worked: Total regular hours
  - Overtime Hours: Extra hours worked
  - Status: present, absent, late, or leave
  - Notes: Additional information
- **Function**: Daily attendance tracking for payroll calculation

#### Salary Payments
- **Purpose**: Record actual salary payments made
- **Fields**:
  - Salary: Which salary record is being paid
  - Amount: Amount paid
  - Payment Date: When payment was made
  - Payment Method: transfer, cash, check, etc.
  - Reference: Transaction reference
  - Notes: Payment details
- **Function**: Payment history and audit trail

### How It Works
1. Add employees with their details and base salary
2. Record daily attendance (check-in/check-out times)
3. At end of pay period, create salary record
4. System calculates net salary automatically
5. Approve salary and record payment
6. View salary history and payment records

### Key Benefits
- Automates salary calculations reducing errors
- Tracks attendance for accurate overtime payment
- Maintains complete payment history
- Helps with budgeting and financial planning
- Ensures compliance with payroll regulations

---

## 3. Operational Expenses Module (Biaya Operasional)

### Purpose
The Operational Expenses module tracks all business expenses, categorizes them, and manages approval workflows. It helps monitor spending and control costs.

### Components

#### Expense Categories
- **Purpose**: Group expenses by type for better tracking
- **Fields**:
  - Name: Category name (e.g., Utilities, Rent, Marketing)
  - Description: What this category covers
  - Color: Visual identifier for reports
- **Function**: Organize expenses into logical groups

#### Operational Expenses
- **Purpose**: Record individual expense transactions
- **Fields**:
  - Category: Which category the expense belongs to
  - Title: Brief description of expense
  - Description: Detailed explanation
  - Amount: Cost of expense
  - Expense Date: When expense occurred
  - Receipt: Receipt or invoice reference
  - Reference: Purchase order or invoice number
  - Status: pending, approved, or rejected
  - Notes: Additional details
- **Function**: Complete expense tracking with approval workflow

### How It Works
1. Create expense categories to organize spending
2. When an expense occurs, record it with details
3. Attach receipt or reference documents
4. Submit for approval (status = pending)
5. Manager reviews and approves or rejects
6. Track approved expenses for financial reporting

### Key Benefits
- Complete visibility into all business expenses
- Approval workflow prevents unauthorized spending
- Categorized expenses help identify spending patterns
- Supports budget management and cost control
- Provides audit trail for financial audits

---

## Integration with Existing System

### POS Integration
- **Inventory**: POS sales automatically reduce stock quantities
- **Stock Movements**: POS orders create "out" stock movements
- **Products**: Menu items can be linked to inventory products

### Financial Integration
- **Operational Expenses**: Can be exported to accounting systems
- **Payroll**: Salary payments can be recorded in financial reports
- **Revenue**: POS revenue can be compared against expenses

### Reporting
- All modules support data export for custom reports
- Combined data enables comprehensive business analytics
- Historical data supports trend analysis and forecasting

---

## Next Steps for Implementation

1. **Run Migrations**: Execute `php artisan migrate` to create database tables
2. **Seed Initial Data**: Create default expense categories and inventory categories
3. **Add Navigation**: Update dashboard to include links to ERP modules
4. **Create Forms**: Add create/edit forms for better data entry
5. **Add Permissions**: Configure role-based access control for each module
6. **Implement Validation**: Enhance form validation for data integrity
7. **Add Dashboard Widgets**: Create summary widgets for each module
8. **Export Features**: Add CSV/PDF export for reports

---

## Access Routes

### Inventory
- `/inventory` - Products list
- `/inventory/categories` - Categories management
- `/inventory/stock-movements` - Stock movement history

### Payroll
- `/payroll` - Employee overview
- `/payroll/employees` - Employee management
- `/payroll/salaries` - Salary records
- `/payroll/attendance` - Attendance records

### Expenses
- `/expenses` - Expenses list
- `/expenses/categories` - Category management
