# Deployment Guide

## 1. Purpose

This guide covers the server environment and operational tasks needed to deploy FastPhunzira in a production setting.

## 2. Server Requirements

### Recommended Stack
* PHP 8.2+
* MySQL 8+
* Apache or Nginx
* HTTPS enabled
* Git for deployment workflow

### Optional Tools
* Composer
* Mail service or SMTP provider
* Backup automation
* Monitoring/logging system

## 3. Environment Configuration

Use environment variables for:
* database host, name, user, password
* app URL
* mail settings
* certificate verification base URL
* security configuration values

Keep secrets out of source-control files.

## 4. Web Server Configuration

### Apache
Configure the web root to serve the `public` directory and ensure URL rewriting is enabled if required.

### Nginx
Set the document root to the `public` directory and route requests through the application entry point.

## 5. Database Setup

1. Create the database and user
2. Run migration scripts in order
3. Seed roles and permissions
4. Verify schema integrity and indexes
5. Back up before major release activity

## 6. Storage and Permissions

Ensure directories such as:
* storage/
* logs/
* uploads/
* certificates/

have the correct write permissions for the web server user.

## 7. HTTPS and Security

* Require HTTPS on production
* Use secure cookies and session configuration
* Disable debug output in production
* Restrict unnecessary public file access

## 8. Deployment Steps

1. Pull latest code from the release branch
2. Install or update PHP dependencies
3. Load environment variables
4. Run database migrations
5. Validate application startup
6. Check critical user flows manually
7. Confirm HTTPS and security settings

## 9. Backup and Recovery

* Schedule database backups
* Store backups in a secure location
* Test restore procedures periodically
* Keep logs for operational review

## 10. Rollback Plan

If a deployment fails:
* revert to the previous release tag
* restore database from backup if needed
* re-check environment config and file permissions
* validate the most critical flows again before re-launching

## 11. Operational Notes

The production environment should remain simple, secure, and configurable. FastPhunzira should be deployed in a way that allows easy rollback, clear monitoring, and safe ongoing maintenance.
