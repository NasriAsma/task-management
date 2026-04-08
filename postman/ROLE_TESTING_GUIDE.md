# Guide de Test - Role Authorization Testing

## Fichiers Postman

Deux collections Postman sont disponibles:

1. **task-api.postman_collection.json** - Collection principale (auto-générée depuis les routes)
2. **role-authorization-tests.postman_collection.json** - Collection spécialisée pour tester les rôles (à utiliser)

## Guide d'Utilisation

### 1. Import de la Collection

1. Ouvrir **Postman**
2. Cliquer sur **Import** (en haut à gauche)
3. Sélectionner le fichier `role-authorization-tests.postman_collection.json`
4. Cliquer sur **Import**

### 2. Configuration

Avant de lancer les tests, s'assurer que:

- **Server Laravel**: `php artisan serve` (port 8000)
- **Base URL variable**: `http://127.0.0.1:8000/api` (déjà défini dans la collection)
- **Database**: Bien configurée et accessible (MySQL avec user=root, password=)

### 3. Flux de Test Recommandé

#### Étape 1: Setup - Créer les utilisateurs de test
Exécuter dans cet ordre:
1. `Register Admin User`
2. `Register Manager User`
3. `Register Employee User`

**Après chaque enregistrement**, nott l'ID de l'utilisateur créé depuis la réponse JSON (champ `id`) et mettez à jour les variables Postman:
- {{adminId}} 
- {{managerId}}
- {{employeeId}}

#### Étape 2: Assigner les rôles
Exécuter:
1. `Assign Admin Role` (avec {{adminToken}} - obtenu après login)
2. `Assign Manager Role` 
3. `Assign Employee Role`

**Note**: Ces requêtes nécessitent que {{adminToken}} soit déjà défini.

#### Étape 3: Login et Obtenir les Tokens
Exécuter dans cet ordre:
1. `Login as Admin` 
2. `Login as Manager`
3. `Login as Employee`

**Après chaque login**, copier le token depuis la réponse JSON (champ `data.token` ou `token`) et mettez à jour:
- {{adminToken}}
- {{managerToken}}
- {{employeeToken}}

Pour automatiser: Dans Postman, ajouter dans l'onglet **Tests** de chaque login:
```javascript
var jsonData = pm.response.json();
pm.environment.set("adminToken", jsonData.token);  // Adapter pour manager et employee
pm.environment.set("adminId", jsonData.user.id);
```

#### Étape 4: Tester les Routes Protégées

**Admin Routes** (Folder 3):
- `GET /users` avec {{adminToken}} → **Expect: 200 OK**
- `GET /users` avec {{managerToken}} → **Expect: 403 Forbidden**
- `POST /create-user` avec {{adminToken}} → **Expect: 200/201/422**

**Manager Routes** (Folder 4):
- `POST /team-tasks` avec {{managerToken}} → **Expect: 200/201/422**
- `POST /team-tasks` avec {{employeeToken}} → **Expect: 403 Forbidden**

**Employee Routes** (Folder 5):
- `PUT /tasks/{id}/status` avec {{employeeToken}} → **Expect: 200/422**

#### Étape 5: Tester les Policies
Les tests dans Folder 6 vérifient les règles métier spécifiques des policies.

#### Étape 6: Logout
Terminer avec les 3 logout requests.

---

## Variables Postman

| Variable | Description | Exemple |
|----------|-------------|---------|
| `{{baseUrl}}` | URL API | `http://127.0.0.1:8000/api` |
| `{{adminToken}}` | Bearer token admin | `1\|abc123xyz...` |
| `{{managerToken}}` | Bearer token manager | `2\|def456abc...` |
| `{{employeeToken}}` | Bearer token employee | `3\|ghi789def...` |
| `{{adminId}}` | ID user admin | `1` |
| `{{managerId}}` | ID user manager | `2` |
| `{{employeeId}}` | ID user employee | `3` |
| `{{taskId}}` | ID tâche pour tests | `1` |

Pour modifier les variables:
1. Cliquer sur **Environments** (en haut à droite)
2. Créer un nouvel environment ou éditer celui existant
3. Modifier les valeurs

---

## Résultats Attendus

### ✅ Successful Responses (200, 201, 422)

```json
{
  "message": "User created",
  "user": { "id": 1, "name": "Admin User", "email": "admin@test.com" }
}
```

### ❌ Authorization Failures (403)

```json
{
  "message": "Forbidden - Role required: admin"
}
```

### ❌ Authentication Failures (401)

```json
{
  "message": "Unauthenticated"
}
```

---

## Dépannage

### Problème: 403 Forbidden trop souvent

**Solution**:
1. Vérifier que {{adminToken}} est correctement défini
2. Vérifier que le middleware `role:admin` est activé dans les routes
3. Vérifier dans la DB que `user_role` contient les bonnes associations

```sql
SELECT ur.*, r.name FROM user_role ur
JOIN roles r ON ur.role_id = r.id;
```

### Problème: 401 Unauthenticated

**Solution**:
1. Vérifier que le header `Authorization: Bearer {{token}}` est présent
2. Vérifier que le token n'a pas expiré
3. Relancer un login pour obtenir un nouveau token

### Problème: 422 Validation Error

**Solution**:
1. Vérifier les champs requis de la requête
2. Consulter la réponse d'erreur pour voir les validations
3. Ajuster le body JSON

---

## Scripts de Test Automatique

Pour valider automatiquement les statuts HTTP, ajouter dans l'onglet **Tests** de chaque requête:

```javascript
// Admin route - should be 200
pm.test("Admin can access admin routes", function () {
    pm.expect(pm.response.code).to.be.oneOf([200, 422]);
});

// Manager trying admin route - should be 403
pm.test("Manager cannot access admin routes", function () {
    pm.expect(pm.response.code).to.equal(403);
});

// Unauthenticated - should be 401
pm.test("Unauthenticated user blocked", function () {
    pm.expect(pm.response.code).to.equal(401);
});
```

---

## Commandes Utiles

Démarrer le serveur:
```bash
cd task-management
php artisan serve
```

Vérifier les rôles en DB:
```bash
php artisan tinker
>>> App\Models\User::with('roles')->get();
>>> exit
```

Réinitialiser la DB pour un test propre:
```bash
php artisan migrate:fresh --seed
```
