<?php
session_start();
require_once 'config.php';
if (!isset($_SESSION['user_id'])) {
    header("Location: login_register.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch user notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id); // Corrected bind_param usage
$stmt->execute(); // Corrected execute call
$notifications = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Notifications - Campus Lost & Found</title>
  <link rel="stylesheet" href="user.css">
  <style>
    .notifications-list {
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .notification-card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(5px);
      border-radius: 10px;
      padding: 15px 20px;
      border: 1px solid rgba(255, 255, 255, 0.2);
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      transition: all 0.3s ease;
      position: relative;
      cursor: pointer; /* Indicate it's clickable */
    }
    .notification-card:hover {
      background: rgba(255, 255, 255, 0.15);
      transform: translateY(-2px);
    }
    .notification-card.unread {
      border-left: 5px solid #00BFFF;
      background: rgba(0, 191, 255, 0.15); /* Slightly different background for unread */
    }
    .notification-card.unread::after {
      content: 'NEW';
      position: absolute;
      top: 10px;
      right: 10px;
      background-color: #00BFFF;
      color: white;
      padding: 3px 8px;
      border-radius: 5px;
      font-size: 0.7em;
      font-weight: bold;
    }
    .notification-card p {
      margin-bottom: 5px;
      line-height: 1.5;
    }
    .notification-card .time {
      font-size: 0.85em;
      color: rgba(255, 255, 255, 0.7);
    }
    .notification-card strong {
        color: #00BFFF;
    }
    /* Hide the badge on the card itself when it's read */
    .notification-card:not(.unread)::after {
        display: none;
    }
    .delete-notification-btn {
      position: absolute;
      top: 5px;
      left: 8px;
      background: transparent;
      border: none;
      color: rgba(255, 255, 255, 0.5);
      font-size: 22px;
      cursor: pointer;
      padding: 2px 5px;
      line-height: 1;
    }
    .delete-notification-btn:hover {
      color: #ff4d4d;
    }

  </style>
</head>
<body>
  <div class="container">
    <?php $active_page = 'notifications'; include '_sidebar.php'; ?>

    <main class="main-content">
      <h2>🔔 Your Notifications</h2>
      <div class="notifications-list">
        <?php if ($notifications && $notifications->num_rows > 0): ?>
          <?php while($n = $notifications->fetch_assoc()): ?>
            <?php
              $display_message = htmlspecialchars($n['message']);
              // Highlight the item name within the message for better appeal
              $display_message = preg_replace("/(item\s+')([^']+)(')/i", "$1<strong>$2</strong>$3", $display_message);
            ?>
            <div class="notification-card <?= $n['status'] === 'unread' ? 'unread' : '' ?>" data-notification-id="<?= $n['id'] ?>">
              <button class="delete-notification-btn" title="Delete notification">&times;</button>
              <p><?= $display_message ?></p>
              <span class="time"><?= date("F j, Y, g:i a", strtotime($n['created_at'])) ?></span>
            </div>
          <?php endwhile; ?>
        <?php else: ?>
          <p>No notifications yet.</p>
        <?php endif; ?>
      </div>
    </main>
  </div>
  <script src="script.js"></script>
  <script src="sidebar.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const notificationsList = document.querySelector('.notifications-list');
      const notificationBadge = document.querySelector('.sidebar ul li a[href="notifications.php"] .badge');
      const noNotificationsMessage = document.querySelector('.notifications-list > p');

      if (notificationsList) {
        notificationsList.addEventListener('click', function(e) {
          const deleteButton = e.target.closest('.delete-notification-btn');
          const card = e.target.closest('.notification-card');

          // --- Handle Delete Button Click ---
          if (deleteButton && card) {
            e.stopPropagation(); // Prevent the card click event from firing
            const notificationId = card.dataset.notificationId;

            if (confirm('Are you sure you want to delete this notification?')) {
              fetch('delete_notification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `notification_id=${notificationId}`
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  // Animate and remove the card from the view
                  card.style.transition = 'opacity 0.5s ease';
                  card.style.opacity = '0';
                  setTimeout(() => card.remove(), 500);

                  // Update sidebar badge count
                  if (notificationBadge) {
                    notificationBadge.textContent = data.new_unread_count;
                    if (data.new_unread_count === 0) {
                      notificationBadge.remove();
                    }
                  }
                } else {
                  alert('Error: ' + data.message);
                }
              }).catch(error => console.error('Error:', error));
            }
            return; // Stop further execution
          }

          // --- Handle Mark as Read on Card Click ---
          if (card) {
            const notificationId = card.dataset.notificationId;
            const isUnread = card.classList.contains('unread');

            if (isUnread) {
              // Make an AJAX request to mark as read
              fetch('mark_notification_read.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `notification_id=${notificationId}`
              })
              .then(response => response.json())
              .then(data => {
                if (data.success) {
                  card.classList.remove('unread');
                  // Update the sidebar badge count dynamically
                  if (notificationBadge) {
                    notificationBadge.textContent = data.new_unread_count;
                    if (data.new_unread_count === 0) {
                      notificationBadge.remove(); // Remove the badge entirely if count is 0
                    }
                  }
                } else {
                  console.error('Failed to mark notification as read:', data.message);
                }
              })
              .catch(error => {
                console.error('Error marking notification as read:', error);
              });
            }
          }
        });
      }

      // --- Real-time Updates with Server-Sent Events (SSE) ---
      function initializeSSE() {
        // Find the ID of the newest notification currently on the page
        const firstNotificationCard = notificationsList.querySelector('.notification-card');
        const lastId = firstNotificationCard ? firstNotificationCard.dataset.notificationId : 0;

        // Establish a connection to the SSE endpoint
        const eventSource = new EventSource(`fetch_new_notifications.php?last_id=${lastId}`);

        // Listener for new notifications
        eventSource.addEventListener('new_notification', function(event) {
          const notification = JSON.parse(event.data);

          // If the "No notifications" message is present, remove it
          if (noNotificationsMessage && noNotificationsMessage.parentNode) {
            noNotificationsMessage.remove();
          }

          // Create the new notification card element
          const card = createNotificationCard(notification);

          // Add it to the top of the list with an animation
          notificationsList.prepend(card);
          setTimeout(() => card.classList.add('show'), 10);

          // Update the sidebar notification count (we can just increment it)
          if (notificationBadge) {
            notificationBadge.textContent = parseInt(notificationBadge.textContent || '0', 10) + 1;
          } else {
            // If there was no badge, create it
            const notificationLink = document.querySelector('.sidebar ul li a[href="notifications.php"]');
            if (notificationLink) {
              const newBadge = document.createElement('span');
              newBadge.className = 'badge';
              newBadge.textContent = '1';
              notificationLink.appendChild(newBadge);
            }
          }
        });

        eventSource.onerror = function() {
          // The connection was lost, you might want to try reconnecting after a delay
          console.error('SSE connection lost. It will be automatically retried by the browser.');
          eventSource.close();
          // The browser will automatically try to reconnect every few seconds.
        };
      }

      function createNotificationCard(notification) {
        const card = document.createElement('div');
        card.className = `notification-card ${notification.status === 'unread' ? 'unread' : ''}`;
        card.dataset.notificationId = notification.id;

        let displayMessage = notification.message.replace(/(item\s+')([^']+)(')/i, "$1<strong>$2</strong>$3");

        card.innerHTML = `
          <button class="delete-notification-btn" title="Delete notification">&times;</button>
          <p>${displayMessage}</p>
          <span class="time">${new Date(notification.created_at).toLocaleString('en-US', { month: 'long', day: 'numeric', year: 'numeric', hour: 'numeric', minute: '2-digit' })}</span>
        `;
        return card;
      }

      initializeSSE();
    });
  </script>
</body>
</html>
