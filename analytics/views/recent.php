<h3><?php echo lang('analytics_recently'); ?></h3>
<table class="analytics-panel recent-stats" cellspacing="0">
	<tr>
		<th colspan="2" class="top-left top-right"><?php echo lang('analytics_today')?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col visits"><span class="analytics-stat"><?php echo $visits?></span> <?php echo lang('analytics_visits')?></td>
		<td class="analytics-stat-col pageviews"><span class="analytics-stat"><?php echo $pageviews?></span> <?php echo lang('analytics_pageviews')?></td>
	</tr>
	<tr>
		<td class="analytics-stat-col pages-per-visit bottom-left"><span class="analytics-stat"><?php echo $pages_per_visit?></span> <?php echo lang('analytics_pages_per_visit')?></td>		
		<td class="analytics-stat-col avg-visit bottom-right"><span class="analytics-stat"><?php echo $avg_visit?></span> <?php echo lang('analytics_avg_visit')?></td>
	</tr>
</table>

<table class="analytics-panel recent-stats" cellspacing="0">
	<tr>
		<th colspan="2" class="top-left top-right"><?php echo lang('analytics_yesterday')?></th>
	</tr>
	<tr>
		<td class="analytics-stat-col visits"><span class="analytics-stat"><?php echo $yesterday['visits']?></span> <?php echo lang('analytics_visits')?></td>
		<td class="analytics-stat-col pageviews"><span class="analytics-stat"><?php echo $yesterday['pageviews']?></span> <?php echo lang('analytics_pageviews')?></td>
	</tr>
	<tr>
		<td class="analytics-stat-col pages-per-visit bottom-left"><span class="analytics-stat"><?php echo $yesterday['pages_per_visit']?></span> <?php echo lang('analytics_pages_per_visit')?></td>		
		<td class="analytics-stat-col avg-visit bottom-right"><span class="analytics-stat"><?php echo $yesterday['avg_visit']?></span> <?php echo lang('analytics_avg_visit')?></td>
	</tr>
</table>

<p><?php echo lang('analytics_viewing_profile')?> <a href="https://www.google.com/analytics/reporting/?id=<?php echo $profile?>"><?php echo $daily_cache['profile']['title']?></a></p>