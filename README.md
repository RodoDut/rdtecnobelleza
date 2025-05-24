# RD Tecno Belleza - Plataforma de Gestión de Alquiler de Equipos de Depilación Láser

![RD Tecno Belleza](https://rdtecnobelleza.net/wp-content/uploads/2024/01/logo-rdtecnobelleza.png)

## Descripción General
Este repositorio contiene la personalización de un sitio WordPress para la gestión de reservas y alquileres de equipos de depilación láser, orientado a centros de estética. El sistema integra el plugin **Simply Schedule Appointments** y desarrollos propios para automatizar el proceso de reservas, registro de clientes y administración de alquileres.

---

## ✨ Características Principales

- **Reservas online de equipos de depilación láser** desde la página [Reservas](https://rdtecnobelleza.net/reservas), facilitando la autogestión para centros de estética.
- **Automatización del registro de clientes:**
  - Al recibir una reserva, el sistema verifica si existe un usuario con el correo proporcionado.
  - Si no existe, crea automáticamente un nuevo usuario en WordPress.
- **Almacenamiento estructurado de reservas:**
  - Cada reserva se guarda como una entrada personalizada del tipo **Alquileres** en la base de datos SQL.
  - Permite un seguimiento detallado de cada alquiler y su historial.
- **Integración con formularios personalizados** (Contact Forms) para recopilar información adicional relevante de cada cliente o centro de estética.
- **Flujo seguro y validado:**
  - Prevención de usuarios duplicados.
  - Asociación automática de reservas a usuarios y registros de alquiler.
- **Gestión centralizada:**
  - El administrador puede visualizar y gestionar todos los alquileres y clientes desde el panel de WordPress.

---

## 🛠️ Personalizaciones y Cambios Realizados

- **Creación del Custom Post Type "Alquileres"** para registrar cada reserva de equipo como una entrada independiente.
- **Hook personalizado** sobre Simply Schedule Appointments para interceptar cada nueva reserva y ejecutar la lógica de:
  - Guardar los datos en la base de datos SQL.
  - Crear el usuario si no existe.
  - Asociar la reserva al usuario y al custom post type.
- **Integración con Contact Forms** para capturar datos adicionales requeridos por el negocio.
- **Validación y control de duplicados** para asegurar la integridad de los datos y la experiencia del cliente.

---

## 🚀 Flujo de Reserva y Alquiler

1. El centro de estética accede a la página de reservas y completa el formulario de Simply Schedule Appointments.
2. Al enviar la reserva:
    - Se verifica si el correo ya está registrado como usuario.
    - Si no existe, se crea el usuario automáticamente.
    - Se crea una entrada en el custom post type **Alquileres** con los datos de la reserva.
    - Se almacena toda la información relevante en la base de datos SQL.
3. El administrador puede gestionar reservas, alquileres y usuarios desde el panel de WordPress.

---

## 📦 Estructura del repositorio

- `wp-content/themes/hostinger-ai-theme-child/` → Tema hijo personalizado con hooks y lógica de integración.
- `wp-content/plugins/` → Plugins personalizados y configuraciones específicas.
- `.gitignore` → Configuración para proteger archivos sensibles y evitar subir el core de WordPress.

---

## 💡 Notas de seguridad
- **No se sube ningún archivo sensible** (como `wp-config.php` o datos de usuarios) al repositorio.
- El código está preparado para ser desplegado en un entorno WordPress seguro y actualizado.

---

## 📞 Contacto
¿Tienes dudas o quieres saber más? Visita [rdtecnobelleza.net](https://rdtecnobelleza.net) o contacta a través del formulario de la web.

[![Contactar por WhatsApp](https://img.shields.io/badge/WhatsApp-Contactar-25D366?logo=whatsapp&logoColor=white)](https://wa.me/5493415795765?text=Te%20contacto%20porque%20he%20le%C3%ADdo%20tu%20repositorio%20de%20rdtecnobelleza%20en%20github)

---

> Proyecto desarrollado y personalizado por RD-Tecno, de Rodolfo Duttweiler.

---

# RD Tecno Belleza - Laser Equipment Rental Management Platform

![RD Tecno Belleza](https://rdtecnobelleza.net/wp-content/uploads/2024/01/logo-rdtecnobelleza.png)

## General Description
This repository contains the customization of a WordPress site for managing reservations and rentals of laser hair removal equipment, aimed at beauty centers. The system integrates the **Simply Schedule Appointments** plugin and custom developments to automate the reservation process, client registration, and rental administration.

---

## ✨ Main Features

- **Online reservations for laser hair removal equipment** from the [Reservations](https://rdtecnobelleza.net/reservas) page, enabling self-management for beauty centers.
- **Automated client registration:**
  - When a reservation is received, the system checks if a user with the provided email exists.
  - If not, it automatically creates a new WordPress user.
- **Structured reservation storage:**
  - Each reservation is saved as a custom post type entry (**Alquileres**) in the SQL database.
  - Allows detailed tracking of each rental and its history.
- **Integration with custom forms** (Contact Forms) to collect additional relevant information from each client or beauty center.
- **Secure and validated workflow:**
  - Prevention of duplicate users.
  - Automatic association of reservations to users and rental records.
- **Centralized management:**
  - The administrator can view and manage all rentals and clients from the WordPress dashboard.

---

## 🛠️ Customizations and Changes

- **Creation of the "Alquileres" Custom Post Type** to register each equipment reservation as an independent entry.
- **Custom hook** on Simply Schedule Appointments to intercept each new reservation and execute the logic to:
  - Save the data in the SQL database.
  - Create the user if it does not exist.
  - Associate the reservation with the user and the custom post type.
- **Integration with Contact Forms** to capture additional data required by the business.
- **Validation and duplicate control** to ensure data integrity and client experience.

---

## 🚀 Reservation and Rental Workflow

1. The beauty center accesses the reservations page and completes the Simply Schedule Appointments form.
2. Upon submitting the reservation:
    - The system checks if the email is already registered as a user.
    - If not, the user is created automatically.
    - An entry is created in the **Alquileres** custom post type with the reservation data.
    - All relevant information is stored in the SQL database.
3. The administrator can manage reservations, rentals, and users from the WordPress dashboard.

---

## 📦 Repository Structure

- `wp-content/themes/hostinger-ai-theme-child/` → Custom child theme with integration hooks and logic.
- `wp-content/plugins/` → Custom plugins and specific configurations.
- `.gitignore` → Configuration to protect sensitive files and avoid uploading WordPress core files.

---

## 💡 Security Notes
- **No sensitive files** (such as `wp-config.php` or user data) are uploaded to the repository.
- The code is ready to be deployed in a secure and updated WordPress environment.

---

## 📞 Contact
Questions or want to know more? Visit [rdtecnobelleza.net](https://rdtecnobelleza.net) or contact us through the website form.

[![Contactar por WhatsApp](https://img.shields.io/badge/WhatsApp-Contactar-25D366?logo=whatsapp&logoColor=white)](https://wa.me/5493415795765?text=I%20contact%20you%20because%20I've%20been%20read%20your%20rdtecnobelleza%20repository%20on%20github)

---

> Project developed and customized by Rodolfo Duttweiler of RD-Tecno.
