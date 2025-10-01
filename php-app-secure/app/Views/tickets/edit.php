<!-- app/Views/tickets/edit.php -->

<!-- Ошибки сервера -->
<?php if (!empty($errors)): ?>
  <div class="errors">
    <?php foreach ($errors as $e): ?>
      <p><?= htmlspecialchars($e) ?></p>
    <?php endforeach; ?>
  </div>
<?php endif; ?>

<!-- Клиентские ошибки -->
<div id="formErrors" class="errors"></div>

<div class="tui-window">
<form id="editForm" action="/ticket/edit" method="post" novalidate>
  <fieldset class="tui-fieldset tui-border-double" style="text-align:left;">
  <legend>Edit Ticket [#<?= htmlspecialchars($old['id']) ?>]</legend>
  <input 
    type="hidden" 
    name="id" 
    value="
    <?= htmlspecialchars($old['id']) ?>"> 
    
    <!-- //// это ваще зачем тут нужно?  -->

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
    <select multiple class="tui-input" name="category" id="category" required>
      <?php foreach (['Server','Administration','Network','Other'] as $cat): ?>
        <option value="<?= $cat ?>"
          <?= (isset($old['category']) && $old['category'] === $cat) ? 'selected' : '' ?>>
          <?= $cat ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  
  <div class="form-row">
  <label>Priority........:</label>
    <?php foreach (['Low','Medium','High'] as $p): ?>
      <label class="tui-radio">
        <input
          type="radio"
          name="priority"
          id="priority_<?= strtolower($p) ?>"
          value="<?= $p ?>"
          <?= (isset($old['priority']) && $old['priority'] === $p) ? 'checked' : '' ?>
          required>
        <?= $p ?>
        <span></span>
      </label>
    <?php endforeach; ?>
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

  <div class="form-row">
  <label>Status..........:</label>
    <select multiple class="tui-input" name="status" id="status" required>
      <?php foreach (['Pending','Open','Closed'] as $s): ?>
        <option value="<?= $s ?>"
          <?= ($old['status'] === $s) ? 'selected' : '' ?>>
          <?= $s ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <div class="center">
  <button class="tui-button" type="submit">Save Changes</button>
  </div>
</form>
</fieldset>
</div>

<script>
(function(){
  const form = document.getElementById('editForm');
  const errorsDiv = document.getElementById('formErrors');

  form.addEventListener('submit', function(e) {
    errorsDiv.innerHTML = '';
    const errs = [];

    const title = document.getElementById('title').value.trim();
    if (!title) {
      errs.push('Title is required.');
    } else if (title.length > 255) {
      errs.push('Title must be 255 characters or fewer.');
    }

    const description = document.getElementById('description').value.trim();
    if (!description) {
      errs.push('Description is required.');
    }

    const category = document.getElementById('category').value;
    if (!category) {
      errs.push('Category must be selected.');
    }

    if (!form.priority.value) {
      errs.push('Please choose a priority.');
    }

    const dueDateValue = document.getElementById('due_date').value;
    if (!dueDateValue) {
      errs.push('Due date is required.');
    } else {
      const dueDate = new Date(dueDateValue);
      const today = new Date();
      today.setHours(0,0,0,0);
      if (dueDate < today) {
        errs.push('Due date cannot be in the past.');
      }
    }

    const status = document.getElementById('status').value;
    if (!['Pending','Open','Closed'].includes(status)) {
      errs.push('Invalid status selected.');
    }

    if (errs.length) {
      e.preventDefault();
      errorsDiv.innerHTML = errs.map(msg => '<p>'+msg+'</p>').join('');
      errorsDiv.scrollIntoView({ behavior: 'smooth' });
    }
  });
})();
</script>
