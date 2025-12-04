# Laravel 11 Payment Gateway Integration (Razorpay)

**By:** Manasi Patel  
**Date:** 2025  
**Laravel Version:** 11  

This project demonstrates a **Payment Management System** using Laravel 11 and Razorpay. Users can make payments, view active payments, and manage them with **soft delete and restore** functionality.  

---

## Features

- Payment form using Razorpay  
- Save payments in database (pending → success)  
- Display payments list (only active)  
- Soft delete payments with popup confirmation  
- Restore deleted payments (optional)  
- Centered Bootstrap design  

---

## Prerequisites

- PHP >= 8.1  
- Composer  
- MySQL or MariaDB  
- Laravel 11  

---

## Installation & Setup

### 1. Install Laravel 11 & Navigate to Project

```bash
composer create-project laravel/laravel laravel11-payment "^11.0"
cd laravel11-payment
2. Configure Database
Update your .env file:

env

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=payment_app
DB_USERNAME=root
DB_PASSWORD=
Create the database:

sql

CREATE DATABASE payment_app;
3. Install Razorpay SDK
bash

composer require razorpay/razorpay
4. Create Payments Table Migration
bash

php artisan make:migration create_payments_table --create=payments
Edit the migration file (database/migrations/xxxx_create_payments_table.php) and include the following columns:

id → Primary key

amount → Decimal, max 10 digits, 2 decimal places

payment_method → String, nullable

status → String, default pending

created_by → Unsigned Big Integer, nullable

updated_by → Unsigned Big Integer, nullable

timestamps → Laravel created_at & updated_at

softDeletes → For soft delete functionality (deleted_at)

Run the migration:

bash
php artisan migrate
5. Create Payment Model
bash

php artisan make:model Payment
Model Notes:

Use SoftDeletes trait for soft delete functionality.

Fillable fields: amount, payment_method, status, created_by, updated_by.

6. Create Payment Controller
bash

php artisan make:controller PaymentController
Controller Responsibilities:

Display payment form

Process payment via Razorpay API

Handle payment success callback

List all payments

Soft delete a payment

Restore a soft-deleted payment

7. Environment Variables for Razorpay
Add Razorpay test keys to .env:

env

RAZORPAY_KEY=rzp_test_xxxxxxxxxxxxx
RAZORPAY_SECRET=xxxxxxxxxxxxxxxx
Replace with your actual Razorpay test keys.

8. Run the Application
bash

php artisan serve
Visit in browser:

bash

http://localhost:8000/payment
Workflow
User enters payment amount in the payment form.

On submission, a Razorpay order is created via API.

Payment is recorded in the database with status = pending.

Razorpay checkout popup opens.

After successful payment, payment status is updated to success.

Admin can view all payments.

Admin can soft delete payments or restore them.

All Commands in One Place
bash

# 1. Install Laravel 11
composer create-project laravel/laravel laravel11-payment "^11.0"
cd laravel11-payment

# 2. Install Razorpay SDK
composer require razorpay/razorpay

# 3. Create Migration for Payments Table
php artisan make:migration create_payments_table --create=payments

# 4. Run Migrations
php artisan migrate

# 5. Create Payment Model
php artisan make:model Payment

# 6. Create Payment Controller
php artisan make:controller PaymentController

# 7. Run Laravel Server
php artisan serve
✅ Congratulations!
