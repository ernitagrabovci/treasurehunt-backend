<div x-show="isQuizOpen" class="modal">
  <p x-text="currentQuestion"></p>
  <template x-for="(answer, idx) in currentAnswers">
    <button @click="submitAnswer(idx)" x-text="answer"></button>
  </template>
</div>

<div x-show="isSuccessOpen" class="modal">
  <p>congratulations! you found the treasure.</p>
</div>