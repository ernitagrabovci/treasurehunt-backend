# game engine flow

## how the game works

1. user logs in
2. page loads first 360° scene
3. user clicks on invisible treasure
4. quiz modal opens
5. user answers correctly
6. "congratulations" modal appears
7. game saves progress

## main alpine.js component

put this in `resources/js/alpine/game-state.js`:

```javascript
Alpine.data('gameState', () => ({
  isQuizOpen: false,
  isSuccessOpen: false,
  currentQuestion: null,
  currentAnswers: [],
  
  async onHotspotClick(hotspot) {
    if (hotspot.type === 'treasure') {
      this.currentQuestion = hotspot.data.question.en;
      this.currentAnswers = hotspot.data.answers.map(a => a.text.en);
      this.isQuizOpen = true;
    }
  },
  
  async submitAnswer(selectedIndex) {
    const isCorrect = this.currentAnswers[selectedIndex] === this.correctAnswer;
    
    if (isCorrect) {
      await axios.post('/api/treasures/found', {
        hotspot_id: this.activeHotspotId
      });
      this.isQuizOpen = false;
      this.isSuccessOpen = true;
      setTimeout(() => this.isSuccessOpen = false, 3000);
    } else {
      alert('wrong answer, try again');
    }
  }
}));