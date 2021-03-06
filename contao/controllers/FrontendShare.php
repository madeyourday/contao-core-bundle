<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2014 Leo Feyer
 *
 * @package Core
 * @link    https://contao.org
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL
 */

namespace Contao;


/**
 * Class FrontendShare
 *
 * Share a page via a social network.
 * @copyright  Leo Feyer 2005-2014
 * @author     Leo Feyer <https://contao.org>
 * @package    Core
 */
class FrontendShare extends \Frontend
{

	/**
	 * Run the controller
	 */
	public function run()
	{
		switch (\Input::get('p'))
		{
			case 'facebook':
				$query  = '?u=' . rawurlencode(\Input::get('u', true));
				$query .= '&t=' . rawurlencode(\Input::get('t', true));
				$query .= '&display=popup';
				$query .= '&redirect_uri=http%3A%2F%2Fwww.facebook.com';
				header('Location: http://www.facebook.com/sharer/sharer.php' . $query);
				exit; break;

			case 'twitter':
				$query  = '?url=' . rawurlencode(\Input::get('u', true));
				$query .= '&text=' . rawurlencode(\Input::get('t', true));
				header('Location: http://twitter.com/share' . $query);
				exit; break;

			case 'gplus':
				$query  = '?url=' . rawurlencode(\Input::get('u', true));
				header('Location: https://plus.google.com/share' . $query);
				exit; break;

			default:
				header('HTTP/1.1 301 Moved Permanently');
				header('Location: ../index.php');
				exit;
		}
	}
}
