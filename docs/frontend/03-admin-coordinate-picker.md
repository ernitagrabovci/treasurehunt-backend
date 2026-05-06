# admin coordinate picker

## what it does

admin clicks anywhere on a 360° image → automatically captures coordinates for a new hotspot.

## the alpine.js code

put this in `resources/js/alpine/editor-coord-picker.js`:

```javascript
function coordPicker() {
  return {
    pitch: 0,
    yaw: 0,
    type: 'treasure',
    viewer: null,

    initPannellum() {
      this.viewer = pannellum.viewer('panorama', {
        type: 'equirectangular',
        panorama: '/storage/scenes/current-editor-scene.webp'
      });

      // capture click coordinates
      this.viewer.on('mousedown', (event) => {
        const coords = this.viewer.mouseEventToCoords(event);
        if (coords) {
          this.pitch = coords[0];
          this.yaw = coords[1];
        }
      });
    },

    saveHotspot() {
      axios.post('/admin/hotspots', {
        scene_id: this.currentSceneId,
        type: this.type,
        pitch: this.pitch,
        yaw: this.yaw
      }).then(() => {
        alert('hotspot saved');
      });
    }
  }
}