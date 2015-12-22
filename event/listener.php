<?php
/**
*
* @package phpBB Extension - Username Colour Changer
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\usernamecolourchanger\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
* Event listener
*/
class listener implements EventSubscriberInterface
{
	/** @var \phpbb\request\request */
	protected $request;

	/** @var \phpbb\template\template */
	protected $template;

	/** @var \phpbb\user */
	protected $user;

	/** @var \phpbb\auth\auth */
	protected $auth;

	/** @var \phpbb\db\driver\driver */
	protected $db;

	/** @var \phpbb\config\config */
	protected $config;

	/**
	* Constructor
	*
	* @param \phpbb\request\request				$request
	* @param \phpbb\template\template			$template
	* @param \phpbb\user						$user
	* @param \phpbb\auth\auth					$auth
	* @param \phpbb\db\driver\driver			$db
	* @param \phpbb\config\config				$config
	*/
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\auth\auth $auth, \phpbb\db\driver\driver_interface $db, \phpbb\config\config $config)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
		$this->db = $db;
		$this->config = $config;
	}

	/**
	* Assign functions defined in this class to event listeners in the core
	*
	* @return array
	* @static
	* @access public
	*/
	static public function getSubscribedEvents()
	{
		return array(
			'core.ucp_profile_modify_profile_info'		=> 'modify_profile_info',
			'core.ucp_profile_validate_profile_info'	=> 'validate_profile_info',
			'core.ucp_profile_info_modify_sql_ary'		=> 'info_modify_sql_ary',
		);
	}

	/**
	* Allow to change their colour
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function modify_profile_info($event)
	{
		$this->user->add_lang_ext('dmzx/usernamecolourchanger', 'common');

		// Request the user option vars and add them to the data array
		$event['data'] = array_merge($event['data'], array(
			'user_colour'	=> $this->request->variable('user_colour', $this->user->data['user_colour'], true),
		));

		$this->template->assign_vars(array(
			'COLOUR'		=> $event['data']['user_colour'],
			'USE_USERNAMECOLOURCHANGER'	 	=> $this->auth->acl_get('u_usernamecolourchanger_use'),
		));
	}

	/**
	* Validate changes to their colour
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function validate_profile_info($event)
	{
		$array = $event['error'];

		if (!function_exists('validate_data'))
		{
			include($this->root_path . 'includes/functions_user.' . $this->php_ext);
		}
		$validate_array = array(
			'user_colour'		=> array('string', true, 3, 6),
		);
		$error = validate_data($event['data'], $validate_array);
		$event['error'] = array_merge($array, $error);
	}

	/**
	* Changed their colour so update the database
	*
	* @param object $event The event object
	* @return null
	* @access public
	*/
	public function info_modify_sql_ary($event)
	{
		// user is changing their colour so update the topics table to reflect that
		$this->update_tables($event['data']['user_colour']);

		$event['sql_ary'] = array_merge($event['sql_ary'], array(
			'user_colour' => $event['data']['user_colour'],
		));
	}

	/**
	* Update topics table
	* @param object $user_colour The colour of the user chosen in the UCP
	* @return null
	* @access private
	*/
	private function update_tables($user_colour)
	{
		$sql_ary = array(
			'topic_last_poster_colour'	=> $user_colour,
		);
		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE topic_last_poster_id = ' . $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$sql_ary = array(
			'topic_first_poster_colour'	=> $user_colour,
		);
		$sql = 'UPDATE ' . TOPICS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE topic_poster = ' . $this->user->data['user_id'];
		$this->db->sql_query($sql);

		$sql_ary = array(
			'forum_last_poster_colour'	=> $user_colour,
		);
		$sql = 'UPDATE ' . FORUMS_TABLE . ' SET ' . $this->db->sql_build_array('UPDATE', $sql_ary) . ' WHERE forum_last_poster_id = ' . $this->user->data['user_id'];
		$this->db->sql_query($sql);

		if ($this->config['newest_user_id'] == $this->user->data['user_id'])
		{
			set_config('newest_user_colour', $user_colour, true);
		}
		return;
	}
}
