<?php foreach ($blogs as $data): ?>
	<i class="fa fa-pencil"></i>
	<b><a href="/blog/active?act=blogs&amp;uz=<?=$data['blogs_user']?>"><?=nickname($data['blogs_user'])?></a></b> (<?=$data['cnt']?>)<br />
<?php endforeach; ?>

<br /><br />Всего пользователей: <b><?=$total?></b>