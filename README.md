# Woliba API – Code Challenge

This project implements a secure backend API that supports passwordless authentication (Magic Link and OTP), profile completion, and wellness modules.

---

## Features

### Invitation & Authentication Flow

1. **Invite a User (Admin only)**
   - When an admin invites a user, the system normalizes the email and evaluates the invitation state:
      - **No existing invitation** - A new invitation is created with a token and expiry. An email is sent if `is_magic_link = true`.
      - **Already used** - The invitation cannot be reused, and no email is sent.
      - **Still valid and unused** - The system prevents resending to avoid duplicate invites.
      - **Expired but unused** - The token is rotated, names are updated, and a new email is sent (only if `is_magic_link = true`).
   - If `is_magic_link = false`, the invitation is stored in the database, but no email is ever sent.
   - The API always responds with a generic success message, so clients cannot determine whether an email address exists in the system. 
   
#### a) Invite User via a magic link (is_magic_link = true)
   - ![Invite User via Magic Link](z_docs/Screenshots/1.%20Magic%20Link%20Invite.png)

#### b) Invite User without a magic link (is_magic_link = false)
- ![Invite User without Magic Link](z_docs/Screenshots/2.%20Invite%20Without%20Magic%20Link.png)

---
2. **Verify Magic Link**
    - User clicks on signed URL.
    - Validates signature, burns token.
    - Creates or finds user from invitation.
    - Returns JWT + limited profile.
  - `Note: The Magic Link can be retrieved from the application logs or from the invitation email after a successful API call.`

- ![Verify Magic Link](z_docs/Screenshots/3.%20Magic%20Link%20Verify.png)

---
3. **Verify Email**
    - User provides email.
    - System checks if an invitation exists.
    - Returns limited profile if found.
   
- `Note: The invitation email address can be retrieved from the application logs or the database after a successful API call.`
- ![Verify Email](z_docs/Screenshots/4.%20Verify%20Email.png)

---
4. **Send OTP**
    - Available **only after successful email verification** for users invited by admin.
    - Generates a 6-digit OTP, stores it, and sends to the verified email.
   

   - ![Send OTP](z_docs/Screenshots/5.%20Send%20OTP.png)

---
5. **Verify OTP**
    - User submits email + OTP.
    - Create or find a user from invitation.
    - Validates and burns OTP.
    - Returns JWT + limited profile.
- `Note: The OTP is stored in the database and also logged after a successful API call`

- ![Verify OTP](z_docs/Screenshots/6.%20Verify%20OTP.png)

---

### Profile Update

After authentication, users must be registration by providing:
- Password
- Date of Birth (MM/DD/YYYY)
- Contact number
- Confirmation flag


- ![Profile Update ](z_docs/Screenshots/7.%20Update%20Profile.png)


---

### Wellness Interests
- Users select at least one or more multiple interests (e.g., Yoga, Meditation, Fitness).
- Stored in pivot table **`user_wellness_interest`**.

### a) List Wellness Interests
- ![List Wellness Interests](z_docs/Screenshots/8.%20List%20Wellness%20Interests.png)

### b) Save User Wellness Interests
- ![Save User Wellness Interests](z_docs/Screenshots/9.%20Save%20User%20Wellness%20Interests.png)

---
### Wellbeing Pillars
- Users must select exactly **three (3) pillars** from (Physical, Mental, Social, Financial, Emotional, etc).
- Stored in pivot table **`user_wellbeing_pillar`**.
- Saving pillars marks **`registration_complete = true`**.

### a) List Wellbeing Pillars
- ![List Wellbeing Pillars](z_docs/Screenshots/10.%20List%20Wellbeing%20Pillars.png)

### b) Save User Wellbeing Pillars and Complete User Registration
- ![Save User Wellbeing Pillars](z_docs/Screenshots/11.%20Save%20User%20Wellbeing%20Pillar%20and%20Registration%20is%20completed.png)



---

## Technologies & Libraries

- **Laravel Framework** 11.31
- **PHP** 8.4 (via Herd)
- **JWT Auth**: tymon/jwt-auth
- **Database**: MySQL
- **Mail**: MailHog (local SMTP testing)
- **Testing**: PestPHP 4, Mockery
- **Tools**: PhpStorm + Laravel Idea, Herd, DBngin, Postman (API testing), TablePlus, Figma (flow charts), SQLFlow (ER diagrams), ChatGPT + Google for exploration and research.

---

## Prerequisites

Before setting up the project, ensure you have the following installed and configured on your system:

- **PHP 8.4+** – Required for running the Laravel 11 application.
- **Composer 2.x** – For managing PHP dependencies.
- **MySQL 8.x** – Primary database used by the API.
- **MailHog** – Local SMTP server for testing email (OTP and Magic Link).
- **Postman** – For API testing using the provided collection.

Note: A Postman Collection with the corresponding environment is included in the `z_docs` directory.
---


## Setup Instructions

```bash
# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Generate app key & JWT secret
php artisan key:generate
php artisan jwt:secret

# Run migrations & seeders
php artisan migrate --seed

# Serve application
php artisan serve
```

## Conclusion

This project delivers a secure backend API with passwordless authentication (Magic Link and OTP), profile completion, and wellness modules.  
It follows a clean structure using services and actions, with tests to ensure reliability and maintainability.

The API is fully functional and can be used as a foundation for future growth.  
Further improvements that could be implemented include:
- Request throttling to reduce probing.
- More consistent error responses across endpoints.
- Richer, user-friendly email templates.
- Using Redis for OTP storage and expiry management instead of the database for better performance.  