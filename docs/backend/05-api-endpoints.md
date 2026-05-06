# api endpoints

all endpoints require authentication (except login).

## authentication

| method | url | what it does |
|--------|-----|--------------|
| post | /api/login | email + password → login |
| post | /api/logout | logout |
| get | /api/user | get logged in user data |

## game endpoints

| method | url | what it does |
|--------|-----|--------------|
| get | /api/game/initial-scene | get first scene + all hotspots |
| get | /api/game/scene/{id} | get specific scene + its hotspots |
| get | /api/treasures/check/{hotspotId} | check if user already found this treasure |
| post | /api/treasures/found | save that user found a treasure |

## admin endpoints (only for role=admin)

| method | url | what it does |
|--------|-----|--------------|
| post | /admin/hotspots | create new hotspot |
| put | /admin/hotspots/{id} | update hotspot |
| delete | /admin/hotspots/{id} | delete hotspot |
| post | /admin/scenes | upload new 360° scene |

## example response from /api/game/initial-scene

```json
{
  "scene": {
    "id": 1,
    "title": {"en": "living room", "sq": "dhoma e ndenjes"},
    "image_path": "/storage/scenes/living-room.webp",
    "is_initial": true
  },
  "hotspots": [
    {
      "id": 42,
      "type": "treasure",
      "pitch": 12.34,
      "yaw": -23.45,
      "data": {
        "question": {"en": "what is 2+2?", "sq": "sa eshte 2+2?"},
        "answers": [
          {"text": {"en": "4", "sq": "4"}, "correct": true},
          {"text": {"en": "5", "sq": "5"}, "correct": false}
        ]
      }
    }
  ]
}