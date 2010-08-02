<h3><?php echo lang('analytics_lastmonth'); ?></h3>
<table class="analytics-panel last-month" cellspacing="0">
	<tr>
		<th colspan="3" class="top-left top-right"><?php echo lang('analytics_overview')?></th>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $lastmonth['visits']?></span> <?php echo lang('analytics_visits')?></td>
		<td class="analytics-sparkline"><?php echo $lastmonth['visits_sparkline']?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $lastmonth['pageviews']?></span> <?php echo lang('analytics_pageviews')?></td>
		<td class="analytics-sparkline"><?php echo $lastmonth['pageviews_sparkline']?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?php echo $lastmonth['pages_per_visit']?></span> <?php echo lang('analytics_pages_per_visit')?></td>
		<td class="analytics-sparkline"><?php echo $lastmonth['pages_per_visit_sparkline']?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?=$lastmonth['bounce_rate']?></span> <?php echo lang('analytics_bounce_rate')?></td>
		<td class="analytics-sparkline"><?php echo $lastmonth['bounce_rate_sparkline']?></td>
	</tr>
	<tr>
		<td class="analytics-stat-row"><span class="analytics-stat"><?=$lastmonth['avg_visit']?></span> <?php echo lang('analytics_avg_visit')?></td>
		<td class="analytics-sparkline"><?php echo $lastmonth['avg_visit_sparkline']?></td>	
	</tr>
	<tr>
		<td class="analytics-stat-row bottom-left cap"><span class="analytics-stat"><?php echo $lastmonth['new_visits']?></span> <?php echo lang('analytics_new_visits')?></td>
		<td class="analytics-sparkline bottom-right cap"><?php echo $lastmonth['new_visits_sparkline']?></td>	
	</tr>
</table>

<p><?php echo $lastmonth['date_span']?></p>