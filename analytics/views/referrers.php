<h3><?php echo lang('analytics_top_referrers'); ?></h3>

<table class="analytics-panel analytics-reports" cellspacing="0">
	<tr>
		<th class="top-left"><?php echo lang('analytics_referrer')?></th>
		<th class="top-right"><?php echo lang('analytics_visits')?></th>			
	</tr>
<?php foreach($lastmonth['referrers'] as $result): ?>
	<tr>
		<td class="analytics-top-referrer-row"><?php echo $result['title']?></td>
		<td class="analytics-count"><?php echo $result['count']?></td>
	</tr>
<?php endforeach; ?>
	<tr>
		<td class="analytics-report-link bottom-left bottom-right cap" colspan="2"><a href="https://www.google.com/analytics/reporting/sources?id=<?php echo $profile['id']; ?>" target="_blank"><?php echo lang('analytics_more');?></a></td>
	</tr>
</table>	