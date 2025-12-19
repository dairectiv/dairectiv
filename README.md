<a id="readme-top"></a>

<!-- PROJECT LOGO -->
<br />
<div align="center">
  <h1 align="center">dairectiv</h1>

  <p align="center">
    An AI enablement hub for engineering teams that provides a single source of truth for authoring, versioning, and governing AI guidance (rules, commands, skills, playbooks, subagents). Syncs this guidance into native formats for various AI dev tools (AGENTS.md, Cursor rules, Claude Code, JetBrains AI Assistant, OpenAI Codex).
    <br />
    <a href="CLAUDE.md"><strong>Explore the docs »</strong></a>
  </p>
</div>



<!-- TABLE OF CONTENTS -->
<details>
  <summary>Table of Contents</summary>
  <ol>
    <li>
      <a href="#built-with">Built With</a>
    </li>
    <li>
      <a href="#getting-started">Getting Started</a>
      <ul>
        <li><a href="#prerequisites">Prerequisites</a></li>
        <li><a href="#installation">Installation</a></li>
      </ul>
    </li>
    <li><a href="#usage">Usage</a></li>
    <li><a href="#development">Development</a></li>
  </ol>
</details>



<!-- ABOUT THE PROJECT -->
## Built With

* [![PHP][PHP.com]][PHP-url]
* [![Symfony][Symfony.com]][Symfony-url]
* [![PostgreSQL][PostgreSQL.com]][PostgreSQL-url]
* [![FrankenPHP][FrankenPHP.com]][FrankenPHP-url]
* [![Doctrine][Doctrine.com]][Doctrine-url]
* [![Docker][Docker.com]][Docker-url]

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- GETTING STARTED -->
## Getting Started

This section explains how to set up and run dairectiv for the first time. You'll install the required dependencies, configure your environment, and start the application.

### Prerequisites

Before setting up the project, make sure the following tools are installed on your machine:
* **Docker & Docker Compose**: Used to run the core services and dependencies in isolated containers.
  - [Install Docker on Linux](https://docs.docker.com/engine/install/ubuntu/)
  - [Install Docker on MacOS](https://orbstack.dev/)
* **Castor**: A lightweight and modern task runner for PHP.
  - [Install Castor](https://castor.jolicode.com/installation/)

### Installation

1. Clone the repo
    ```sh
    git clone https://github.com/dairectiv/dairectiv.git
    cd dairectiv
    ```
2. Setup environment variables in `api/.env.local` (optional, for custom configuration)
   ```dotenv
   # Example: Override database settings, app secret, etc.
   DATABASE_URL="postgresql://dairectiv:dairectiv@localhost:40010/dairectiv?serverVersion=16&charset=utf8"
   ```
3. Build containers, install dependencies, and reset database
    ```sh
    castor start
    ```

The application will be available at the configured `DEFAULT_URI` (check `api/.env` for the default value).

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- USAGE -->
## Usage

Once installed, you can use the following commands to interact with the project:

### Quick Commands

- **Start the infrastructure**: `castor up`
- **Stop the infrastructure**: `castor stop`
- **View logs**: `castor logs`
- **Run tests**: `castor test`
- **Check code quality**: `castor qa`

For a complete list of available commands, run:
```sh
castor list
```

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- DEVELOPMENT -->
## Development

For detailed development instructions including:
- Architecture and directory structure
- Database management commands
- Quality assurance tools (PHPStan, Rector, ECS, PHPUnit)
- Git & Linear workflow
- Creating entities and controllers
- Contributing to Castor tasks

Please refer to the [CLAUDE.md](CLAUDE.md) documentation.

<p align="right">(<a href="#readme-top">back to top</a>)</p>



<!-- MARKDOWN LINKS & IMAGES -->
[PHP.com]: https://img.shields.io/badge/php-777BB4?logo=php&style=for-the-badge&logoColor=white
[PHP-url]: https://www.php.net/
[Symfony.com]: https://img.shields.io/badge/Symfony-black?logo=symfony&style=for-the-badge
[Symfony-url]: https://symfony.com/
[PostgreSQL.com]: https://img.shields.io/badge/Postgres-316192?style=for-the-badge&logo=postgresql&logoColor=white
[PostgreSQL-url]: https://www.postgresql.org/
[FrankenPHP.com]: https://img.shields.io/badge/FrankenPHP-4F46E5?style=for-the-badge&logo=php&logoColor=white
[FrankenPHP-url]: https://frankenphp.dev/
[Doctrine.com]: https://img.shields.io/badge/Doctrine-FC6A31?style=for-the-badge&logo=doctrine&logoColor=white
[Doctrine-url]: https://www.doctrine-project.org/
[Docker.com]: https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white
[Docker-url]: https://www.docker.com/
