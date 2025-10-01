<div class="tui-window">
  <fieldset class="tui-fieldset">
<legend><?= htmlspecialchars($title) ?></legend> 
<fieldset class="tui-fieldset tui-border-solid no-legend">
<table class="ticket-info-table">
  <tr><th>ID..............:</th><td><?= htmlspecialchars($ticket['id']) ?></td></tr>
  <tr><th>Title...........:</th><td><?= htmlspecialchars($ticket['title']) ?></td></tr>
  <tr><th>Description.....:</th><td><pre><?= htmlspecialchars($ticket['description']) ?></pre></td></tr>
  <tr><th>Category........:</th><td><?= htmlspecialchars($ticket['category']) ?></td></tr>
  <tr><th>Priority........:</th><td><?= htmlspecialchars($ticket['priority']) ?></td></tr>
  <tr><th>Due Date........:</th><td><?= htmlspecialchars($ticket['due_date']) ?></td></tr>
  <tr><th>Urgent..........:</th><td><?= $ticket['is_urgent'] ? 'Yes' : 'No' ?></td></tr>
  <tr><th>Status..........:</th><td><?= htmlspecialchars($ticket['status']) ?></td></tr>
  <tr><th>Created At......:</th><td><?= htmlspecialchars($ticket['created_at']) ?></td></tr>
</table>
</fieldset>

<?php if($isAdmin): ?>
  <div class="ticket-edit-row">
    <div style="display:flex; gap: .5rem;">
  <a href="/ticket/edit?id=<?=$ticket['id']?>"><button class="tui-button">Edit</button></a>
  <a href="/ticket/delete?id=<?=$ticket['id']?>"
       onclick="return confirm('Delete?')"><button class="tui-button red-168">Delete</button></a>
       </div>
<div style="display:flex; gap: .5rem;">
  <form action="/ticket/status" method="post">
    <div style="display:flex; gap: .5rem;">
    <input type="hidden" name="id" value="<?=$ticket['id']?>">
    <select class="tui-input" name="status">
      <?php foreach(['Pending','Open','Closed'] as $s): ?>
        <option value="<?=$s?>" <?=$ticket['status']===$s?'selected':''?>>
          <?=$s?>
        </option>
      <?php endforeach;?>
    </select>
    <button class="tui-button orange-255 black-168-text" type="submit">Change Status</button>
    </div>
  </form>
  </div>
  </div>
<?php endif; ?>
</fieldset>
</div>

<p><a href="/tickets">&larr;<button class="tui-button blue-168 white-255-text">← Back to list</button></a></p>