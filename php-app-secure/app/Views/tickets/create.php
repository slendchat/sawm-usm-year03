<!-- app/Views/tickets/create.php -->

<div class="tui-window">
  <form id="ticketForm" action="/ticket/create" method="post" novalidate>
  <fieldset class="tui-fieldset tui-border-solid" style="text-align:left;">
  <legend><?= htmlspecialchars($title) ?></legend>
  <div class="form-row">
  <label>Title...........:</label>
    <input
      class="tui-input" 
      type="text" 
      name="title"
      id="title"
      value="<?= htmlspecialchars($old['title'] ?? '') ?>"
      required 
      maxlength="255"
      placeholder="Up to 255 chars">
    </div>
  
  <div class="form-row">
  <label>Description.....:</label>
    <textarea
      class="tui-input"  
      name="description" 
      id="description"
      required
      placeholder="Describe the issue…"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
  </div>

  <div class="form-row">
  <label>Category........:</label>
    <select multiple class="tui-input" name="category" id="category" required style="width:100%;">
      <?php foreach(['Server','Administration','Network','Other'] as $cat): ?>
        <option value="<?= $cat?>"
          <?= (isset($old['category']) && $old['category']===$cat)?'selected':''?>>
          <?= $cat?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  
  <div class="form-row">
  <label>Priority........:</label>
  <div>
  <?php foreach(['Low','Medium','High'] as $p): ?>
      <label class="tui-radio">
        <input 
          type="radio" 
          name="priority" 
          id="priority_<?= strtolower($p) ?>" 
          value="<?= $p?>"
          <?= (isset($old['priority']) && $old['priority']===$p)?'checked':''?> 
          required>
        <?= $p?>
        <span></span>
      </label>
    <?php endforeach; ?>
  </div>
    </div>
<div class="form-row">
  <label>Due Date........:</label>
    <input
      class="tui-input"
      type="date" 
      name="due_date" 
      id="due_date"
      value="<?= htmlspecialchars($old['due_date'] ?? '') ?>" 
      required>
  

  <label class="tui-checkbox">
    <input
      type="checkbox" 
      name="is_urgent" 
      id="is_urgent" 
      value="1"
      <?= !empty($old['is_urgent']) ? 'checked' : '' ?>>
    Mark as urgent
    <span></span>
  </label>
  </div>
  <div class="center">
    <button class="tui-button" type="submit">Create Ticket</button>
  </div>
  </fieldset>
  </form>
</div>

<!-- сюда будем выводить клиентские ошибки -->

<div id="tui-error" class="tui-window red-168" style="margin-bottom: 2rem;">
  <fieldset class="tui-fieldset">
    <legend class="yellow-255-text">Alert</legend>
    <div id="formErrors" class="errors"></div>
  </fieldset>
</div>

<script>
(function(){
  const form = document.getElementById('ticketForm');
  const errorsDiv = document.getElementById('formErrors');
  const errorWindow = document.getElementById('tui-error');

  // Изначально скрываем окно ошибок
  errorWindow.style.display = 'none';

  form.addEventListener('submit', function(e) {
    errorsDiv.innerHTML = '';
    const errs = [];

    // Валидация полей
    const title = document.getElementById('title').value.trim();
    if (!title) {
      errs.push('• Title is required');
    } else if (title.length > 255) {
      errs.push('• Title must be 255 characters or fewer');
    }

    const description = document.getElementById('description').value.trim();
    if (!description) {
      errs.push('• Description is required');
    }

    const category = document.getElementById('category').value;
    if (!category) {
      errs.push('• Category must be selected');
    }

    if (!form.priority.value) {
      errs.push('• Please choose a priority');
    }

    const dueDateValue = document.getElementById('due_date').value;
    if (!dueDateValue) {
      errs.push('• Due date is required');
    } else {
      const dueDate = new Date(dueDateValue);
      const today = new Date();
      today.setHours(0,0,0,0);
      if (dueDate < today) {
        errs.push('• Due date cannot be in the past');
      }
    }

    if (errs.length) {
      e.preventDefault();
      errorsDiv.innerHTML = errs.join('<br>');
      errorWindow.style.display = 'block'; // Показываем окно ошибок
      errorWindow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      errorWindow.style.display = 'none'; // Скрываем окно ошибок
    }
  });
})();
</script>
