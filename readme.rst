###################
What is Mutases
###################

**Mutases** is a **CodeIgniter 3–based application** designed to manage
student transfer (mutation) data and school administrative processes.

This repository represents the **development version (v2)**,
with a focus on improved PHP compatibility, better application stability,
and a cleaner, more maintainable code structure.

----

Release Information
===================

This repository contains the source code for **Mutases v2**, which has been
tested and adjusted to run on the latest stable PHP version recommended
for CodeIgniter 3 environments.

----

Changelog & New Features
=======================

- Upgraded PHP compatibility from **PHP 5.6** to **PHP 7.4**
- Refactored code structure for improved stability and maintainability
- Updated libraries to ensure safe and optimal execution on PHP 7.4

----

Server Requirements
===================

**PHP 7.4 (Recommended & Used)**

- PHP >= 7.4
- CodeIgniter 3
- MySQL / MariaDB
- Apache or Nginx
- Composer

.. note::

   CodeIgniter 3 officially supports PHP 5.6 as the minimum requirement.
   However, **Mutases v2** is optimized and actively run on **PHP 7.4**
   to provide better security, performance, and overall compatibility.

   Starting from this version, **Mutases v2** has been renamed to
   **SimsGTK (Student and Teacher Information System)**.

----

Installation
============

1. Clone this repository into your web server directory.

2. Before running Composer, ensure your ``composer.json`` is configured
   as follows:

.. code-block:: json

   {
     "description": "The CodeIgniter framework",
     "name": "codeigniter/framework",
     "type": "project",
     "homepage": "https://codeigniter.com",
     "license": "MIT",
     "require": {
       "php": ">=5.3.7",
       "phpoffice/phpspreadsheet": "1.23.*",
       "psr/simple-cache": "^1.0",
       "smalot/pdfparser": "^2.12"
     },
     "config": {
       "platform-check": false,
       "audit": {
         "block-insecure": false
       }
     }
   }

3. Install dependencies using Composer:

.. code-block:: bash

   composer install --no-scripts

----

WhatsApp Integration (Optional)
===============================

Mutases provides optional WhatsApp notification support using
a background worker and helper function.

WA Worker Setup
---------------

Create a file named ``wa_worker.php`` in the **root Mutases folder**:

.. code-block:: php

   <?php

   $queue_file = __DIR__ . "/wa_queue.txt";

   while (true) {

       if (!file_exists($queue_file)) {
           sleep(1);
           continue;
       }

       $lines = file($queue_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
       file_put_contents($queue_file, ""); // clear queue

       foreach ($lines as $line) {
           $data = json_decode($line, true);

           // WhatsApp server configuration
           $url   = "YOUR_WA_SERVER_URL";
           $token = "YOUR_API_TOKEN";

           $payload = json_encode([
               "number"  => $data['number'],
               "message" => $data['message']
           ]);

           $ch = curl_init($url);
           curl_setopt($ch, CURLOPT_HTTPHEADER, [
               "Content-Type: application/json",
               "token: " . $token
           ]);
           curl_setopt($ch, CURLOPT_POST, true);
           curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
           curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
           curl_exec($ch);
           curl_close($ch);
       }

       sleep(2); // check queue every 2 seconds
   }

WA Helper Setup
---------------

Create a helper file at ``application/helpers/wa_helper.php``:

.. code-block:: php

   <?php if (!defined('BASEPATH')) exit('No direct script access allowed');

   function send_wa($number, $message)
   {
       $url   = "YOUR_WA_SERVER_URL";
       $token = "YOUR_API_TOKEN";

       $data = [
           "number"  => $number,
           "message" => $message
       ];

       $payload = json_encode($data);

       $ch = curl_init($url);
       curl_setopt($ch, CURLOPT_HTTPHEADER, [
           "Content-Type: application/json",
           "token: " . $token
       ]);
       curl_setopt($ch, CURLOPT_POST, true);
       curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
       curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

       $result = curl_exec($ch);
       curl_close($ch);

       return $result;
   }

.. warning::

   Make sure ``wa_worker.php`` and ``wa_helper.php`` are excluded from
   version control if they contain sensitive credentials
   (API tokens, server URLs).
