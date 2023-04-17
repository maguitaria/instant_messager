<?php
include 'main.php';
if (!is_loggedin($pdo)) {
    exit('error');
}
// update the account status to Idle
$stmt = $pdo->prepare('update accounts set status = "Idle" where id = ?');
$stmt->execute([$_SESSION['account_id']]);
// retrieve conversations associated with user along with the most recent msg
$stmt = $pdo->prepare('select c.*  ,  select msg from messages where conversation_id = c.id order by submit_date desc limit 1 AS msg, (select submit_date from messages where conversation_id = c.id order by submit_date desc limit 1) 
AS msg_date, a.full_name AS account_sender_full_name, a2.full_name AS account_receiver_full_name from conversations c
JOIN accounts a ON a.id = c.account_sender_id JOIN accounts a2 ON a2.id = c.account_receiver_id
WHERE c.account_sender_id = ? OR c.account_receiver_id = ? GROUP BY c.id');
$stmt->execute([$_SESSION['account_id'], $_SESSION['account_id']]);
$conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);
// sort conversations by the most recent message time
usort($conversations, function ($a, $b) {
    $date_a = strtotime($a['msg_date'] ? $a['msg_date'] : $a['submit_date']);
    $date_b = strtotime($b['msg_date'] ? $b['msg_date'] : $b['submit_date']);
    return $date_b - $date_a;
});
?>
// conversation template below
<div class="chat-widget-conversations">
<a href="#" class="chat-widget-new-conversation" data-id="<?=$conversation['id']?>">&plus; New Chat </a>
<?php foreach ($conversations as $conversation) :  ?>
<a href="#" class="chat-widget-conversation" data-id="<?=$conversation['id']?>">
        <div class="icon" <?='style="background-color: ' . color_from_string($conversation['account_sender_id'] 
        != $_SESSION['account_id'] ? $conversation['account_sender_full_name'] : $conversation['account_receiver_full_name']) . '"'?>><?=substr($conversation['account_sender_id'] 
        != $_SESSION['account_id'] ? $conversation['account_sender_full_name'] : $conversation['account_receiver_full_name'], 0, 1)?></div>
    <div class="details">
        <div class="title">
            
        </div>
    </div>
</div>
