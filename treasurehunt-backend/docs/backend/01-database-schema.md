# database schema (source of truth)

all tables use `id` as unsigned big integer primary key.  
timestamps: `created_at`, `updated_at` unless noted.

---

## table: `users`

| column | type | description |
|--------|------|-------------|
| id | bigint unsigned | pk |
| name | string | display name |
| email | string | unique, login |
| password | string | hashed |
| role | enum('admin','user') | default 'user' |
| locale | char(2) | 'sq' or 'en', default 'en' |
| remember_token | string nullable | |
| timestamps | | |

---

## table: `scenes`

| column | type | description |
|--------|------|-------------|
| id | bigint unsigned | pk |
| title | json | multilingual title e.g. `{"en":"living room","sq":"dhoma e ndenjes"}` |
| image_path | string | path to 360° webp image (local or s3) |
| is_initial | boolean | default false, only one scene set to true |
| timestamps | | |

---

## table: `hotspots`

| column | type | description |
|--------|------|-------------|
| id | bigint unsigned | pk |
| scene_id | foreign | references `scenes.id` on cascade |
| type | enum('nav','treasure') | navigation door or treasure |
| pitch | decimal(6,4) | pannellum coordinate |
| yaw | decimal(6,4) | pannellum coordinate |
| target_scene_id | foreign nullable | for navigation type, references `scenes.id` |
| data | json | treasure type: `{"question":{"en":"...","sq":"..."},"answers":[{"text":{"en":"...","sq":"..."},"correct":false}]}` |
| timestamps | | |

**validation rule:**  
- if `type = 'nav'` → `target_scene_id` required, `data` null  
- if `type = 'treasure'` → `target_scene_id` null, `data` required with question/answers

---

## table: `user_treasure`

| column | type | description |
|--------|------|-------------|
| id | bigint unsigned | pk |
| user_id | foreign | references `users.id` on cascade |
| hotspot_id | foreign | references `hotspots.id` on cascade |
| found_at | timestamp | defaults to now |
| unique constraint | | (`user_id`, `hotspot_id`) prevents double discovery |

---

## example queries

### get initial scene with hotspots (for game engine)

```sql
select s.*, 
       h.id as hotspot_id, 
       h.type, 
       h.pitch, 
       h.yaw, 
       h.target_scene_id,
       h.data
from scenes s
left join hotspots h on h.scene_id = s.id
where s.is_initial = true;