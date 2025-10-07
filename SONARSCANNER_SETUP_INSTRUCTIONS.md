# SonarScanner Setup Instructions for CREAMS

## ✅ Configuration File Created
The `sonar-project.properties` file has been created in your project root.

---

## 📦 Installation Options

### **Option 1: Using Chocolatey (Recommended - Requires Admin)**

Open **PowerShell or CMD as Administrator** and run:

```powershell
choco install sonarqube-scanner.portable -y
```

After installation, restart your terminal and verify:
```powershell
sonar-scanner --version
```

---

### **Option 2: Manual Installation (No Admin Required)**

1. **Download SonarScanner**
   - Visit: https://docs.sonarsource.com/sonarqube/latest/analyzing-source-code/scanners/sonarscanner/
   - Download: `sonar-scanner-cli-<version>-windows-x64.zip`

2. **Extract and Setup**
   ```powershell
   # Extract to a location (e.g., C:\Tools\sonar-scanner)
   # Add to PATH environment variable:
   #   C:\Tools\sonar-scanner\bin
   ```

3. **Verify Installation**
   ```powershell
   sonar-scanner --version
   ```

---

### **Option 3: Using SonarCloud (Online - No Local Install)**

SonarCloud is a free online service for open-source projects.

1. Visit: https://sonarcloud.io/
2. Sign up with GitHub account
3. Connect your repository
4. Use GitHub Actions for automatic scanning

---

## 🚀 Running SonarScanner

### **Local Scan (SonarQube Server Required)**

If you have a local SonarQube server running:

```bash
sonar-scanner -Dsonar.host.url=http://localhost:9000 -Dsonar.token=YOUR_TOKEN
```

### **SonarCloud Scan**

```bash
sonar-scanner -Dsonar.organization=your-org -Dsonar.host.url=https://sonarcloud.io -Dsonar.token=YOUR_TOKEN
```

---

## 🎯 Quick Start Without Installation

### **Using Docker (Easiest for Quick Scan)**

If you have Docker installed:

```powershell
# Pull SonarScanner Docker image
docker pull sonarsource/sonar-scanner-cli

# Run scan in current directory
docker run --rm -v "C:\laragon\www\CREAMS:/usr/src" sonarsource/sonar-scanner-cli -Dsonar.host.url=http://host.docker.internal:9000 -Dsonar.token=YOUR_TOKEN
```

---

## 📊 What You Need to Scan

### **For Local SonarQube Server**
1. Install SonarQube Server (separate installation)
2. Start SonarQube Server
3. Generate authentication token
4. Run scanner

### **For SonarCloud (Recommended for Testing)**
1. Sign up at https://sonarcloud.io/
2. Import your GitHub repository
3. Generate token from SonarCloud
4. Run scanner or use GitHub Actions

---

## 🛠️ Alternative: Static Analysis Without SonarScanner

You can still perform code quality checks using PHP tools:

### **PHPStan (Static Analysis)**
```bash
composer require --dev phpstan/phpstan
./vendor/bin/phpstan analyse app
```

### **PHP_CodeSniffer (Coding Standards)**
```bash
composer require --dev squizlabs/php_codesniffer
./vendor/bin/phpcs app --standard=PSR12
```

### **PHPMD (Mess Detector)**
```bash
composer require --dev phpmd/phpmd
./vendor/bin/phpmd app text cleancode,codesize,controversial,design,naming,unusedcode
```

### **Larastan (Laravel-specific Static Analysis)**
```bash
composer require --dev nunomaduro/larastan
./vendor/bin/phpstan analyse
```

---

## 📝 Current Project Configuration

Your `sonar-project.properties` is configured to scan:
- **Source**: app, config, database, resources, routes
- **Tests**: development/tests
- **Excluded**: vendor, node_modules, storage, migrations, seeders
- **PHP Version**: 8.1

---

## 🎯 Recommended Next Steps

**Option A: Use SonarCloud (Easiest)**
1. Go to https://sonarcloud.io/
2. Sign in with GitHub
3. Import CREAMS repository
4. Get automatic scans on every push

**Option B: Install Locally**
1. Run as Administrator: `choco install sonarqube-scanner.portable -y`
2. Setup local SonarQube server OR use SonarCloud
3. Run scan: `sonar-scanner`

**Option C: Use Alternative PHP Tools (Immediate)**
1. Install PHPStan: `composer require --dev phpstan/phpstan`
2. Run analysis: `./vendor/bin/phpstan analyse app`

---

## ❓ Which Option Should You Choose?

- **For quick setup**: Use **PHPStan/Larastan** (no server needed)
- **For comprehensive analysis**: Use **SonarCloud** (free, online)
- **For enterprise setup**: Install **local SonarQube** server

Would you like me to proceed with **Option C (PHPStan)** for immediate code analysis?
