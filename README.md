# SmartStockers.pk

SmartStockers.pk is a robust, responsive, and feature-rich **inventory and shipment management web application** built to streamline e-commerce order processing, stock tracking, and logistics. Designed for businesses managing orders from platforms like **WooCommerce** and **Shopify**, it also integrates with major courier services like **Leopards**, **PostEx**, and **TCS** for seamless order fulfillment.

🌐 **Live Site:** [https://smartstockers.pk](https://smartstockers.pk)

---

## 🔐 Authentication

* **Login System**

  * Secure login for both **Admin** and **Clients**
  * Session-based access control
  * Change password functionality for all users

---

## 🧑‍💼 User Roles

### 🔸 Admin Panel

* Dashboard with statistics and quick actions
* Manage Clients (Add/Edit/Delete)
* View and manage all orders
* Add or fetch products from WooCommerce / Shopify
* Track all return orders, print orders, and stock invoices
* Access full shipment and stock reports (monthly filter)
* Assign stockers and track what stocker handled which order
* Create orders directly on:

  * **Leopards**
  * **PostEx**
  * **TCS**

### 🔹 Client Panel

* Dashboard with their specific orders and status
* Upload WooCommerce / Shopify orders
* View product catalog
* Place manual orders
* View and download invoices
* Return or print orders

---

## 📦 Orders

* **WooCommerce & Shopify Integration**

  * Automatically fetch orders using API/webhooks
  * Map fetched orders to internal database
* **Order Management**

  * Manual order creation with client, product, COD options
  * Orders status from 0 to 6 (e.g., pending, packed, shipped, returned)
* **Order Fulfillment**

  * Place orders directly on courier APIs (Leopards, TCS, PostEx)
  * View order status and tracking
  * Generate invoices (PDF/Printable)
* **Print Orders Modal**

  * Create batch of orders to print
  * Show total quantities, weight, and subtotals
* **Return Order Modal**

  * View order details
  * Restore product quantities to inventory
  * Change order status to "Returned" (status = 6)

---

## 📦 Stock & Inventory

* **Stock Invoices**

  * Add stock for multiple products
  * View historical stock additions
* **Inventory Management**

  * Track product quantity
  * Show which stocker handled which item/order
* **Monthly Reports**

  * Stock reports by month
  * Shipment reports by client and order status
* **Client-wise Reports**

  * Delivered orders, returned orders, pending orders

---

## 📃 Invoices & Reports

* Auto-generated invoices for:

  * Stock In
  * Delivered Orders
  * Return Orders
* Monthly filtering for:

  * Orders
  * Stock updates
  * Shipping activity
* Exportable and printable formats

---

## 🧑‍💻 Technical Stack

* **Frontend:** HTML, CSS, JavaScript, jQuery, Bootstrap
* **Backend:** PHP (core, not Laravel)
* **Database:** MySQL
* **Integrations:**

  * WooCommerce (via REST API)
  * Shopify (via Admin API)
  * Leopards Courier API
  * PostEx API
  * TCS API

---

## 📱 Responsive Design

* Fully responsive across mobile, tablet, and desktop
* Clean, user-friendly UI/UX for both Admin and Client users

---

## 🔐 Security Features

* Session-based user access
* Secure authentication flow
* Password change functionality for all users

---

## 🚧 Features Overview

| Feature                             | Available |
| ----------------------------------- | --------- |
| Admin & Client login                | ✅         |
| Manual Order Creation               | ✅         |
| WooCommerce & Shopify Order Import  | ✅         |
| Direct Order Creation on Couriers   | ✅         |
| Print Orders Batch System           | ✅         |
| Return Orders Workflow              | ✅         |
| Invoicing (Orders, Returns, Stock)  | ✅         |
| Monthly Reports                     | ✅         |
| Product Stock Management            | ✅         |
| Password Change                     | ✅         |
| Client-specific Order Panel         | ✅         |
| Mobile Responsive                   | ✅         |
| Stocker Tracking (who shipped what) | ✅         |

---

## 🚀 Getting Started

Clone the repository:

```bash
git clone https://github.com/mkamranskd/smartstockers.git
cd smartstockers
```

Set up the database:

* Import the provided SQL file
* Update `/php_action/db_connect.php` with your database credentials

---

## 📜 License

This project is proprietary and all rights are reserved by [Nexvel Hub](https://www.facebook.com/nexvelhub).

---

## 📞 Contact

For support, training, or partnership opportunities:

📧 Email: [info@smartstockers.pk](mailto:admin@smartstockers.pk)
🌐 Website: [https://smartstockers.pk](https://smartstockers.pk)
📘 Facebook: [Nexvel Hub](https://facebook.com/nexvelhub)

---
