<div class="wrap">
    <h1>Scheduled Tasks</h1>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>Task ID</th>
                <th>First Execution Date/Time</th>
                <th>User</th>
                <th>Next Run</th>
                <th>Status</th>
                <th>Execution Success</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tasks as $task) : 
                $user = get_userdata($task->user_id);
            ?>
                <tr>
                    <td><?php echo esc_html($task->task_id); ?></td>
                    <td><?php echo esc_html($task->first_execution); ?></td>
                    <td><?php echo esc_html($user->user_login); ?></td>
                    <td><?php echo esc_html($task->next_run); ?></td>
                    <td><?php echo esc_html($task->status); ?></td>
                    <td><?php echo esc_html($task->execution_success); ?></td>
                    <td>
                        <?php if ($task->user_id === get_current_user_id() || current_user_can('administrator')) : ?>
                            <button class="button button-secondary" onclick="triggerTask(<?php echo esc_attr($task->task_id); ?>)">Trigger Now</button>
                            <button class="button button-secondary" onclick="deleteTask(<?php echo esc_attr($task->task_id); ?>)">Delete</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
    function triggerTask(taskId) {
        if (confirm('Are you sure you want to trigger this task now?')) {
            jQuery.post(ajaxurl, { action: 'html_exporter_manual_trigger', task_id: taskId }, function(response) {
                if (response.success) {
                    alert('Task triggered successfully.');
                } else {
                    alert('Failed to trigger task: ' + response.data);
                }
            });
        }
    }

    function deleteTask(taskId) {
        if (confirm('Are you sure you want to delete this task?')) {
            jQuery.post(ajaxurl, { action: 'html_exporter_remove_schedule', schedule_id: taskId }, function(response) {
                if (response.success) {
                    alert('Task deleted successfully.');
                    location.reload();
                } else {
                    alert('Failed to delete task: ' + response.data);
                }
            });
        }
    }
</script>
