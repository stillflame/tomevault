# 📖 TomeVault API

A RESTful API for managing a mystical catalog of legendary tomes, grimoires, and forbidden books, along with their authors, languages, locations, and spells.

---

## ✨ Features

* 📚 List all tomes with lightweight info (title, author, language, danger level)
* 🔍 Retrieve detailed tome data with nested relationships (author, language, current owner, location, spells)
* ✍️ Create new tomes with flexible referencing of related entities by ID or exact name
* 🛡️ Comprehensive validation and error handling
* 🔐 Supports authentication via Laravel Sanctum tokens
* 🌱 Seeders included for initial data population

---

## 🌐 Base URL

```
https://your-domain.com/api/v1
```

---

## 🔑 Authentication

Use Bearer tokens generated via Laravel Sanctum.

**Example Header:**

```
Authorization: Bearer YOUR_TOKEN_HERE
```

---

## 📦 Endpoints

### 📄 List Tomes

```
GET /tomes
```

Returns a paginated or full list of tomes with minimal fields.

**Sample Response:**

```json
{
  "data": [
    {
      "id": "uuid",
      "title": "The Book of Spells",
      "author": {
        "id": "uuid",
        "name": "Merlin"
      },
      "language_name": "Ancient Greek",
      "danger_level": "Severe",
      "spell_count": 12, 
      "tome_detail_url": "http://tomevault.test/api/v1/tomes/uuid"
    }
  ],
  "meta": {
    "total": 50,
    "per_page": 10,
    "current_page": 1
  }
}
```

### 📖 Get Tome Details

```
GET /tomes/{id}
```

Returns full tome details including nested relationships:

* 🧙 Author
* 🗣️ Language
* 🧾 Current Owner
* 📍 Last Known Location
* ✨ Spells

### ✏️ Create Tome

```
POST /tomes
```

Requires authentication.

**Request Body Example:**

```json
{
  "title": "Necronomicon",
  "alternate_titles": ["Al Azif"],
  "origin": "Ancient Arabia",
  "author": "Abdul Alhazred",
  "language": "Arabic",
  "current_owner": "Merlin",
  "last_known_location": "Camelot",
  "contents_summary": "A book of forbidden knowledge.",
  "cursed": true,
  "sentient": false,
  "danger_level": "Severe",
  "artifact_type": "Grimoire",
  "cover_material": "Goat Skin",
  "pages": 666,
  "illustrated": false,
  "notable_quotes": ["That is not dead which can eternal lie."]
}
```

---

## ✅ Validation Rules

* Related fields (`author`, `language`, etc.) accept either UUID or exact name
* `artifact_type` and `cover_material` use predefined enum values
* Allowed `danger_level` values: `Low`, `Medium`, `High`, `Severe`, `Unknown`

---

## 🛠️ Setup

1. Clone the repository
2. Run `composer install`
3. Configure `.env`
4. Run migrations and seeders:

   ```bash
   php artisan migrate --seed
   ```
5. Start the application:

   ```bash
   php artisan serve
   ```

---

## 🧪 Testing

```bash
php artisan test
```

---

## 📄 License

MIT License
