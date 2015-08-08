<?php
/**
*
* @package phpBB Extension - Username Colour Changer
* @copyright (c) 2015 dmzx - http://www.dmzx-web.net
* @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
*
*/

namespace dmzx\usernamecolourchanger\migrations;

class usernamecolourchanger_schema extends \phpbb\db\migration\migration
{

	public function update_data()
	{
		return array(

			// Add permissions
			array('permission.add', array('u_usernamecolourchanger_use')),

			// Set permissions
			array('permission.permission_set', array('ADMINISTRATORS', 'u_usernamecolourchanger_use', 'group')),
		);
	}
}