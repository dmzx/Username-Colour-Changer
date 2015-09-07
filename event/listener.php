<?php
/**
*
* @package phpBB Extension - Username Colour Changer
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\usernamecolourchanger\event;

/**
* @ignore
*/
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
	
	/**
	 * Constructor
	 *
	 * @param \phpbb\request\request			$request
	 * @param \phpbb\template\template			$template
	 * @param \phpbb\user						$user
	 * @param \phpbb\auth\auth					$auth
	 */
	public function __construct(\phpbb\request\request $request, \phpbb\template\template $template, \phpbb\user $user, \phpbb\auth\auth $auth)
	{
		$this->request = $request;
		$this->template = $template;
		$this->user = $user;
		$this->auth = $auth;
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
		$event['sql_ary'] = array_merge($event['sql_ary'], array(
			'user_colour' => $event['data']['user_colour'],
		));
	}
}