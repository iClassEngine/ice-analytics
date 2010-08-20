<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Google Analytics
 *
 * Forked from http://github.com/amphibian/cp_analytics.ee_addon
 *
 * @package		iClassEngine
 * @author		Eric Barnes & Derek Hogue
 * @copyright	Derek Hogue
 * @link		http://ericlbarnes.com
 * @since		Version 1.0
 */

// ------------------------------------------------------------------------

/**
 * Admin Analytics Controller
 *
 * @subpackage	Controllers
 *
 */
class Admin extends Admin_Controller {
	
	var $analytics_settings = array();
	
	function __construct()
	{
		parent::__construct();
		log_message('debug', 'Analytics Initialized');
		
		$this->lang->load('analytics');
		
		$this->analytics_settings = $this->get_settings();
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Index
	 *
	 */
	function index() 
	{
		$data['nav'] = 'settings';

		if ( ! isset($this->analytics_settings['authenticated']) OR $this->analytics_settings['authenticated'] == 'n')
		{
			redirect('admin/analytics/settings');
		}
		require_once(EXTPATH.'analytics/libraries/gapi.class.php');
		
 		$this->template->title(lang('analytics'));
		$this->template->set_metadata('stylesheet', base_url() . 'includes/addons/analytics/css/accessory.css', 'link');
		
		$ga_settings = $this->analytics_settings;
		$ga_user = $ga_settings['user'];
		$ga_password = base64_decode($ga_settings['password']);
		$ga_profile_id = $ga_settings['profile'];

		// Check to see if we have a hourly cache, and if it's still valid
		if (isset($ga_settings['hourly_cache']['cache_time']) && 
			time() < strtotime('+60 minutes', $ga_settings['hourly_cache']['cache_time']))
		{
			$today = $ga_settings['hourly_cache'];
			$today['hourly_updated'] = date('g:ia', $ga_settings['hourly_cache']['cache_time']);
		}
		else
		{
			$today = $this->fetch_hourly_stats($ga_user, $ga_password, $ga_profile_id);
			$today['hourly_updated'] = date('g:ia', time());
		}
			
		// Check to see if we have a daily cache, and if it's still valid
		if (isset($ga_settings['daily_cache']['cache_date']) && 
			$ga_settings['daily_cache']['cache_date'] == date('Y-m-d', time()))
		{
			$daily = $ga_settings['daily_cache'];
			$daily['daily_updated'] = $ga_settings['daily_cache']['cache_date'];
		}
		else
		{
			$daily = $this->fetch_daily_stats($ga_user, $ga_password, $ga_profile_id);
			$daily['daily_updated'] = date('Y-m-d', time());
		}
		
		$combined = array_merge($today, $daily, $ga_settings);

		if (isset($today) && isset($daily))
		{				
			$data['recent'] = $this->load->view('recent', $combined, TRUE);

			$content = array_merge($combined, $daily['lastmonth']);
			$data['last_month'] = $this->load->view('lastmonth', $content, TRUE);

			$data['content'] = $this->load->view('content', $content, TRUE);
			
			$data['referrers'] = $this->load->view('referrers', $content, TRUE);
			
		}
		else
		{
			// We couldn't fetch our account data for some reason
			$this->sections[$this->lang->line('analytics_trouble_connecting')] = 
				$this->lang->line('analytics_trouble_connecting_message');
		}
	

		$this->template->build('wrapper', $data); 
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Manage the status list
	 *
	 */
	public function settings()
	{
		$data['nav'] = 'settings';
		
		$this->template->title('Analytics Settings');
		
		if (is_array($this->analytics_settings))
		{
			foreach ($this->analytics_settings as $item => $value)
			{
				$data[$item] = $value;
				$settings[$item] = $value;
			}
		}
		else
		{
			$data['user'] = '';
		}

		// If we have a username and password, try and authenticate and fetch our profile list
		if (isset($this->analytics_settings['authenticated']) && $this->analytics_settings['authenticated'] == 'y')
		{
			require_once(EXTPATH.'analytics/libraries/gapi.class.php');				
			$ga_user = $this->analytics_settings['user'];
			$ga_password = base64_decode($this->analytics_settings['password']);
			
			$ga = new gapi($ga_user, $ga_password);
			$ga->requestAccountData(1,100);
			
			if ($ga->getResults())
			{
				$data['ga_profiles'] = array('' => '--');
				foreach($ga->getResults() as $result)
				{
				  $data['ga_profiles'][$result->getProfileId()] = $result->getTitle();
				}
			}
		}
		
		$this->load->helper(array('form', 'url', 'html', 'date'));
		
		$this->load->library('form_validation');
		
		$this->form_validation->set_rules('user', 'lang:analytics_username', 'required');
		$this->form_validation->set_rules('password', 'lang:analytics_password', '');
		$this->form_validation->set_rules('profile', 'lang:analytics_profile', '');
		
		if ($this->form_validation->run() == FALSE)
		{
			$this->template->build('admin/settings', $data); 
		}
		else
		{
			require_once(EXTPATH.'analytics/libraries/gapi.class.php');				
			$ga_user = $this->input->post('user');
			$ga_password = $this->input->post('password');
			if ($ga_user && $ga_password)
			{
				$ga = new gapi($ga_user, $ga_password);
				if($ga->getAuthToken() != FALSE)
				{
					$settings['user'] = $ga_user;
					$settings['password'] = base64_encode($ga_password);
					$settings['authenticated'] = 'y';
				}
				else
				{
					// The credentials don't authenticate, so zero us out
					$settings['user'] = $ga_user;
					$settings['password'] = base64_encode($ga_password);
					$settings['profile'] = '';
					$settings['authenticated'] = 'n';
				}
			}

			if ($profile = $this->input->post('profile'))
			{
				$settings['profile'] = $profile;			
				$settings['hourly_cache'] = '';
				$settings['daily_cache'] = '';
			}

			$this->db->where('module_name', 'analytics');
			$this->db->update('modules', array('module_settings' => serialize($settings)));
			
			$this->session->set_flashdata('msg', lang('lang_settings_saved'));
			
			redirect('admin/analytics/settings');
		}
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Reset credentials
	 */
	public function reset()
	{
		$ga_settings = $this->get_settings();	
		$ga_settings[$site]['user'] = '';
		$ga_settings[$site]['password'] = '';
		$ga_settings[$site]['profile'] = '';
		$ga_settings[$site]['authenticated'] = '';
		
		$this->_update_settings($go_settings);
		redirect('admin/analytics/settings');
		exit;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Get settings
	 */
	public function get_settings($all_sites = FALSE)
	{
		$this->db->select('module_settings')
			->from('modules')
			->where('module_name', 'analytics');
		
		$query = $this->db->get();
		
		$row = $query->row_array(); 
		if ($row['module_settings'] != '')
		{
			$ga_settings = strip_slashes(unserialize($row['module_settings']));
		}
		else
        {
			$ga_settings = array();
		}
		return $ga_settings;
	}
	
	// ------------------------------------------------------------------------
	
	/**
	 * Update the settings table
	 */
	private function _update_settings($ga_settings)
	{
		$this->db->where('module_name', 'analytics');
		$this->db->update('modules', array('module_settings' => serialize($ga_settings)));
	}
	
	// ------------------------------------------------------------------------
	
	public function fetch_hourly_stats($ga_user, $ga_password, $ga_profile_id)
	{
		$data = array();
		$data['cache_time'] = time();					

		$today = new gapi($ga_user,$ga_password);
		$ga_auth_token = $today->getAuthToken();
		$today->requestReportData(
			$ga_profile_id,
			array('date'),
			array('pageviews','visits', 'timeOnSite'),
			'','',
			date('Y-m-d'),
			date('Y-m-d')
		);
		
		$data['visits'] = 
		number_format($today->getVisits());
		
		$data['pageviews'] = 
		number_format($today->getPageviews());
		
		$data['pages_per_visit'] = 
		$this->analytics_avg_pages($today->getPageviews(), $today->getVisits());
		
		$data['avg_visit'] = 
		$this->analytics_avg_visit($today->getTimeOnSite(), $today->getVisits());
		
		// Now cache it
		$ga_settings = $this->get_settings();
		$ga_settings['hourly_cache'] = $data;
		
		$this->_update_settings($ga_settings);

		return $data;
	}

	// ------------------------------------------------------------------------
	
	public function fetch_daily_stats($ga_user, $ga_password, $ga_profile_id)
	{
		$data = array();
		$data['cache_date'] = date('Y-m-d', time());					

		// Compile yesterday's stats
		$yesterday = new gapi($ga_user,$ga_password);
		$ga_auth_token = $yesterday->getAuthToken();
		$yesterday->requestReportData(
			$ga_profile_id,
			array('date'),
			array('pageviews','visits', 'timeOnSite'),
			'','',
			date('Y-m-d', strtotime('yesterday')),
			date('Y-m-d', strtotime('yesterday'))
		);
		
		// Get account data so we can store the profile info
		$data['profile'] = array();
		$yesterday->requestAccountData(1,100);
		foreach($yesterday->getResults() as $result)
		{
			if($result->getProfileId() == $ga_profile_id)
			{
				$data['profile']['id'] = $result->getProfileId();
				$data['profile']['title'] = $result->getTitle();
			}
		}					
		
		$data['yesterday']['visits'] = 
		number_format($yesterday->getVisits());
		
		$data['yesterday']['pageviews'] = 
		number_format($yesterday->getPageviews());
		
		$data['yesterday']['pages_per_visit'] = 
		$this->analytics_avg_pages($yesterday->getPageviews(), $yesterday->getVisits());
		
		$data['yesterday']['avg_visit'] = 
		$this->analytics_avg_visit($yesterday->getTimeOnSite(), $yesterday->getVisits());
		
		// Compile last month's stats
		$lastmonth = new gapi($ga_user,$ga_password,$ga_auth_token);
		$lastmonth->requestReportData($ga_profile_id,
			array('date'),
			array('pageviews','visits', 'newVisits', 'timeOnSite', 'bounces', 'entrances'),
			'date', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday'))
		);
		
		$data['lastmonth']['date_span'] = 
		date('F jS Y', strtotime('31 days ago')).' &ndash; '.date('F jS Y', strtotime('yesterday'));
		
		$data['lastmonth']['visits'] = 
		number_format($lastmonth->getVisits());
		$data['lastmonth']['visits_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'visits');
		
		$data['lastmonth']['pageviews'] = 
		number_format($lastmonth->getPageviews());
		$data['lastmonth']['pageviews_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'pageviews');
		
		$data['lastmonth']['pages_per_visit'] = 
		$this->analytics_avg_pages($lastmonth->getPageviews(), $lastmonth->getVisits());
		$data['lastmonth']['pages_per_visit_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'avgpages');
		
		$data['lastmonth']['avg_visit'] = 
		$this->analytics_avg_visit($lastmonth->getTimeOnSite(), $lastmonth->getVisits());
		$data['lastmonth']['avg_visit_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'time');
		
		$data['lastmonth']['bounce_rate'] = 
		($lastmonth->getBounces() > 0 && $lastmonth->getBounces() > 0) ? 
		round( ($lastmonth->getBounces() / $lastmonth->getEntrances()) * 100, 2 ).'%' : '0%';
		$data['lastmonth']['bounce_rate_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'bouncerate');
		
		$data['lastmonth']['new_visits'] = 
		($lastmonth->getNewVisits() > 0 && $lastmonth->getVisits() > 0) ? 
		round( ($lastmonth->getNewVisits() / $lastmonth->getVisits()) * 100, 2).'%' : '0%';					
		$data['lastmonth']['new_visits_sparkline'] = 
		$this->analytics_sparkline($lastmonth->getResults(), 'newvisits');

		// Compile last month's top content
		$topcontent = new gapi($ga_user,$ga_password,$ga_auth_token);
		$topcontent->requestReportData($ga_profile_id,
			array('hostname', 'pagePath'),
			array('pageviews'),
			'-pageviews', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday')),
			null, 16
		);
		
		$data['lastmonth']['content'] = array();
		$i = 0;
		
		// Make a temporary array to hold page paths
		// (for checking dupes resulting from www vs non-www hostnames)
		$paths = array();
		
		foreach($topcontent->getResults() as $result)
		{
			// Do we already have this page path?
			$dupe_key = array_search($result->getPagePath(), $paths);
			if($dupe_key !== FALSE)
			{
				// Combine the pageviews of the dupes
				$data['lastmonth']['content'][$dupe_key]['count'] = 
				($result->getPageviews() + $data['lastmonth']['content'][$dupe_key]['count']);
			}
			else
			{
				$url = (strlen($result->getPagePath()) > 20) ? 
					substr($result->getPagePath(), 0, 20).'&hellip;' : 
					$result->getPagePath();
				$data['lastmonth']['content'][$i]['title'] = 
				'<a href="http://'.$result->getHostname().$result->getPagePath().'" target="_blank">'.
				$url.'</a>';
				$data['lastmonth']['content'][$i]['count'] = $result->getPageviews();

				// Store the page path at the same position so we can check for dupes
				$paths[$i] = $result->getPagePath();

				$i++;
			}
		}
		
		// Slice down to 8 results
		$data['lastmonth']['content'] = array_slice($data['lastmonth']['content'], 0, 8);
		
		// Compile last month's top referrers
		$referrers = new gapi($ga_user,$ga_password,$ga_auth_token);
		$referrers->requestReportData($ga_profile_id,
			array('source', 'referralPath', 'medium'),
			array('visits'),
			'-visits', '',
			date('Y-m-d', strtotime('31 days ago')),
			date('Y-m-d', strtotime('yesterday')),
			null, 8
		);
		
		$data['lastmonth']['referrers'] = array();
		$i = 0;
		foreach($referrers->getResults() as $result)
		{
			$data['lastmonth']['referrers'][$i]['title'] = 
			($result->getMedium() == 'referral') ?
			'<a href="http://'.$result->getSource() . $result->getReferralPath().'" target="_blank">'.$result->getSource().'</a>' : $result->getSource();
			$data['lastmonth']['referrers'][$i]['count'] = number_format($result->getVisits());
			$i++;
		}
		
		// Now cache it
		$ga_settings = $this->get_settings();
		$ga_settings['daily_cache'] = $data;
		
		$this->_update_settings($ga_settings);
		
		return $data;
	}
	
	// ------------------------------------------------------------------------
	
	public function analytics_avg_pages($pageviews, $visits)
	{
		return ($pageviews > 0 && $visits > 0) ? round($pageviews / $visits, 2) : 0;
	}
	
	// ------------------------------------------------------------------------
	
	public function analytics_avg_visit($seconds, $visits)
	{
		if($seconds > 0 && $visits > 0)
		{
			$avg_secs = $seconds / $visits;
			// This little snippet by Carson McDonald, from his Analytics Dashboard WP plugin
			$hours = floor($avg_secs / (60 * 60));
			$minutes = floor(($avg_secs - ($hours * 60 * 60)) / 60);
			$seconds = $avg_secs - ($minutes * 60) - ($hours * 60 * 60);
			return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
		}
		else
		{
			return '00:00:00';
		}
	}
	
	// ------------------------------------------------------------------------
	
	public function analytics_sparkline($data_array, $metric)
	{
		$max = 0; $stats = array();
		
		foreach($data_array as $result)
		{
			switch($metric) {
				case "pageviews":
					$datapoint = $result->getPageviews();
					break;
				case "visits":	
					$datapoint = $result->getVisits();
					break;
				case "time":
					$datapoint = $result->getTimeOnSite();
					break;
				case "avgpages":
					$datapoint = ($result->getVisits() > 0 && $result->getPageViews() > 0) ? $result->getPageviews() / $result->getVisits() : 0;
					break;
				case "bouncerate":
					$datapoint = ($result->getEntrances() > 0 && $result->getBounces() > 0) ? $result->getBounces() / $result->getEntrances() : 0;
					break;
				case "newvisits":
					$datapoint =  ($result->getNewVisits() > 0 && $result->getVisits() > 0) ? $result->getNewVisits() / $result->getVisits() : 0;
					break;
			}
			$max = ($max < $datapoint) ? $datapoint : $max;
			$stats[] = $datapoint;
		}
		
		return '<img src="http://chart.apis.google.com/chart?cht=ls&amp;chs=120x20&amp;chm=B,FFFFFF66,0,0,0&amp;chco=FFFFFFEE&amp;chf=c,s,FFFFFF00|bg,s,FFFFFF00&chd=t:'.implode(',',$stats).'&amp;chds=0,'.$max.'" alt="" />';
	}
}

/* End of file admin.php */
/* Location: ./upload/includes/addons/cp_analytics/controllers/admin.php */ 