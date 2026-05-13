<div x-data="coordPicker()" x-init="initPannellum()">
  <div id="panorama" style="width:100%; height:500px;"></div>
  
  <form @submit.prevent="saveHotspot">
    <input type="hidden" x-model="pitch">
    <input type="hidden" x-model="yaw">
    <select x-model="type">
      <option value="treasure">treasure</option>
      <option value="nav">navigation door</option>
    </select>
    <button type="submit">save hotspot</button>
  </form>
</div>