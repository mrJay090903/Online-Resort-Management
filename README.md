# Casita De Grands Resort Management System

A comprehensive resort management system for Casita De Grands, featuring booking management, room administration, and payment processing via PayMongo.

## 🚀 Getting Started

### Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Composer
- PayMongo Account

### Installation
1. Clone the repository
2. Install PHP dependencies
3. Set up environment variables
4. Update the `.env` file with your PayMongo API keys and other configuration


### 🔑 Environment Variables

The following environment variables are required:

| Variable | Description |
|----------|-------------|
| `PAYMONGO_SECRET_KEY` | Your PayMongo Secret Key for API authentication |
| `PAYMONGO_PUBLIC_KEY` | Your PayMongo Public Key for client-side operations |

## 📦 Features

- User Authentication & Authorization
- Room Booking Management
- Payment Processing via PayMongo
- Admin Dashboard
- Booking Reports & Analytics
- Customer Feedback System

## 🛠️ Tech Stack

- PHP
- MySQL
- Tailwind CSS
- JavaScript
- PayMongo API

## 🔒 Security

- Environment variables are used for sensitive data
- PayMongo API keys are stored securely
- Input validation and sanitization
- Session management
- CSRF protection

## 📝 Development Guidelines

1. Never commit the `.env` file
2. Always use environment variables for sensitive data
3. Keep the `.env.example` updated with new variables
4. Follow PHP PSR standards for coding style

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Open a pull request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👥 Authors

- **Cope,Jay-ar** - *Fullstock Developer*

## 🙏 Acknowledgments

- PayMongo for payment processing
- Tailwind CSS for styling
- All contributors who have helped with the project
