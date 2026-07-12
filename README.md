# HazardOne

HazardOne is a crowdsourcing mobile application that allows users
to view and report road, environmental, and building hazards.

## Project Components

- Android mobile application developed using Kotlin
- Google Maps and current-location integration
- PHP REST API
- PHP web dashboard
- MySQL database
- Laragon local development server

## Folder Structure

- `mobile-app/` — Android Studio project
- `web-app/` — PHP API and web dashboard
- `database/` — MySQL database structure

## Local Setup

### Database

1. Start Apache and MySQL in Laragon.
2. Open phpMyAdmin.
3. Import `database/hazardone.sql`.

### PHP Application

1. Copy `web-app/config.example.php`.
2. Rename the copy to `config.php`.
3. Enter the local database credentials.
4. Open:

   `http://localhost/HazardOne-System/web-app/`

### Android Application

1. Open `mobile-app` using Android Studio.
2. Add the Maps API key to `local.properties`:

   `MAPS_API_KEY=YOUR_API_KEY`

3. For the Android emulator, use:

   `http://10.0.2.2/HazardOne-System/web-app/`

## Group Members

| Name     | Student Number | Task                                 |
|----------|----------------|--------------------------------------|
| Member 1 | 2025197599     | Mobile application & Web application |
| Member 2 | 2025516823     | Documentation |
| Member 3 | 2025386309     | Documentation |
| Member 4 | 2025910147     | Documentation |
| Member 5 | 2025148265     | Documentation |

## Copyright

Copyright © 2026 One Corporation Team.
All rights reserved.