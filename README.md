# [Документація API за допомогою PostMan](https://documenter.getpostman.com/view/41797928/2sAYX8Hfkn)

# Запуск проекту

## Встановлення необхідних інструментів

Перед запуском проекту необхідно переконатися, що у вас встановлені всі необхідні інструменти:

### **1. Встановлення Composer**
Composer – це менеджер залежностей для PHP. Якщо у вас його ще немає, встановіть його за допомогою:
```sh
scoop install composer
```
Або завантажте вручну з [офіційного сайту](https://getcomposer.org/).

Переконайтеся, що Composer встановлений:
```sh
composer -v
```

### **2. Встановлення Scoop**
Scoop – це менеджер пакетів для Windows, який спрощує встановлення інструментів.

```sh
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
Invoke-RestMethod -Uri https://get.scoop.sh | Invoke-Expression
```

Переконайтеся, що Scoop встановлений:
```sh
scoop -v
```

### **3. Встановлення Symfony CLI**
Symfony CLI використовується для запуску локального сервера і роботи з проєктами Symfony.

```sh
scoop install symfony-cli
```

Переконайтеся, що Symfony встановлений:
```sh
symfony -v
```

---

## **Налаштування та запуск проекту**
### **1. Клонування репозиторію**
Якщо ви працюєте з Git, клонування проекту виконується так:
```sh
git clone <репозиторій>
cd <назва-проекту>
```

### **2. Встановлення залежностей**
Переконайтеся, що всі залежності встановлені:
```sh
composer install
```

### **3. Налаштування файлу `.env`**
Відредагуйте файл `.env` (або `.env.local`) для збереження конфігураційних параметрів.

Якщо ви використовуєте JWT-токени, пропишіть:
```sh
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=<ВАШ_ПАРОЛЬ>
```

### **4. Запуск локального сервера**
```sh
symfony server:start
```
Сервер буде запущений за замовчуванням на `http://127.0.0.1:8000/`.

---

## **Основні команди**
### **Робота з Composer**
- Встановити залежності:  
  ```sh
  composer install
  ```
- Оновити залежності:  
  ```sh
  composer update
  ```
- Додати пакет:  
  ```sh
  composer require <назва_пакету>
  ```

### **⚙Робота з Symfony**
- Запуск сервера:  
  ```sh
  symfony server:start
  ```
- Створення контролера:  
  ```sh
  php bin/console make:controller TestController
  ```
- Очистити кеш:  
  ```sh
  php bin/console cache:clear
  ```

---

## **Доступні API-ендпоїнти**
| Метод  | URL                | Опис                            |
|--------|--------------------|--------------------------------|
| `GET`  | `/api/users`       | Отримати список користувачів   |
| `GET`  | `/api/users/{id}`  | Отримати одного користувача    |
| `POST` | `/api/users`       | Створити нового користувача    |
| `PATCH`| `/api/users/{id}`  | Оновити користувача за ID      |
| `DELETE` | `/api/users/{id}` | Видалити користувача за ID     |

---

## **Додаткова інформація**
- **Symfony офіційна документація:** [https://symfony.com/doc/current/index.html](https://symfony.com/doc/current/index.html)
- **Postman для тестування API:** [https://www.postman.com/](https://www.postman.com/)

---



