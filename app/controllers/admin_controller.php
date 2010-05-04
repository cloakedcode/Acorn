<?php

class AdminController extends AN_Controller
{
	function beforeAction($action)
	{
		if (session_id() == null)
		{
			session_start();
		}

		if ($action !== 'login' && empty($_SESSION['user_id']))
		{
			return 'login';
		}
		else if (empty($_SESSION['ip']) || $_SESSION['ip'] !== self::ip())
		{
			return 'login';
		}
		else if ($action === 'login')
		{
			return 'index';
		}
	}

	function index()
	{
		$this->posts = Post::query('SELECT * FROM #table ORDER BY date DESC');
	}
	
	function login()
	{
		if (isset($_POST['username']) && isset($_POST['password']))
		{
			$user = User::query('SELECT id FROM #table WHERE username=? AND password=?', $_POST['username'], User::hashPassword($_POST['password']));
			if (empty($user) === false)
			{
				$_SESSION['user_id'] = $user[0]->id;
				$_SESSION['ip'] = self::ip();

				header('Location: '.Acorn::toURL('#index'));
				exit;
			}
			else
			{
				$this->msg = 'Username/Password incorrect.';
			}
		}
	}

	function logout()
	{
		session_destroy();

		header('Location: '.Acorn::toURL('#index'));
		exit;
	}

	static private function ip()
	{
		return $_SERVER['REMOTE_ADDR'];
	}
}

?>
