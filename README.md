# Treasure Hunt Game

Treasure Hunt Game is a web-based game developed using PHP, MySQL, JavaScript, HTML and CSS. The game allows players to solve questions through different levels and progress towards the final treasure.

## Features

- User Registration and Login
- Guest Player Support
- Multiple Game Levels
- Question and Answer System
- Hint System
- Coin Reward System
- Player Progress Tracking
- Leaderboard
- Sound and Music Settings
- Session Management

## Game Flow

The player can register, login or continue as a guest. The game consists of multiple levels, and each level contains a question that must be solved to continue.

Players can use hints when required. Coins are earned by completing levels and can be used to obtain hints. The player's level, coins and progress are maintained throughout the game.

After completing all the levels, the player reaches the final treasure.

## Technologies Used

- PHP
- MySQL
- HTML
- CSS
- JavaScript
- XAMPP

## Project Structure

- `ajax/` - Contains backend request handling files.
- `config/` - Contains the database connection file.
- `css/` - Contains stylesheet files.
- `images/` - Contains images used in the game.
- `includes/` - Contains common PHP files such as session, header and footer.
- `js/` - Contains JavaScript files.
- `game.php` - Main Treasure Hunt game page.
- `index.php` - Home page.
- `login.php` - User login page.
- `signup.php` - User registration page.
- `progress.php` - Displays player progress.
- `leaderboard.php` - Displays the leaderboard.
- `instructions.php` - Displays game instructions.

## How to Run the Project

1. Install and start XAMPP.
2. Start Apache and MySQL.
3. Place the `treasure_hunt` folder inside the XAMPP `htdocs` directory.
4. Create the required MySQL database.
5. Configure the database connection in `config/db.php`.
6. Open the project in a web browser using:

   `http://localhost/treasure_hunt/`

## Database

The project uses MySQL for storing user information, questions, levels, coins and player progress.

Database Name: `treasure_hunt_db`

## Purpose

The purpose of this project is to create an interactive Treasure Hunt game where users can solve questions, complete levels, earn coins, use hints and track their progress.

## Author

Suchi Patel
Krishnapriya M.

BCA Project
