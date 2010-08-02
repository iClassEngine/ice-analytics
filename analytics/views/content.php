<h3><?php echo lang('analytics_top_content'); ?></h3>
<table class="analytics-panel analytics-reports" cellspacing="0">
	<tr>
		<th class="top-left">URL</th>
		<th class="top-right"><?php echo lang('analytics_views')?></th>
	</tr>
	<?php foreach($lastmonth['content'] as $result): ?>
	<tr>
		<td class="analytics-top-content-row"><?php echo $result['title']?></td>
		<td class="analytics-count"><?php echo number_format($result['count'])?></td>
	</tr>
	<?php endforeach?>
	<tr>
		<td class="analytics-report-link bottom-left bottom-right cap" colspan="2"><a href="https://www.google.com/analytics/reporting/content?id=<?php echo $profile?>" target="_blank"><?php echo lang('analytics_more')?></a></td>
	</tr>
</table>
