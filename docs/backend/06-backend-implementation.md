# backend implementation

## step 1: create models

### Scene.php
put in `app/Models/Scene.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scene extends Model
{
    protected $fillable = ['title', 'image_path', 'is_initial'];
    
    protected $casts = [
        'title' => 'array',
        'is_initial' => 'boolean'
    ];
    
    public function hotspots()
    {
        return $this->hasMany(Hotspot::class);
    }
}