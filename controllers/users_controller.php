<?php
/**
 * Users Users Controller
 *
 * @package users
 * @subpackage users.controllers
 */
class UsersController extends AppController {

/**
 * Controller name
 *
 * @var string
 */
	public $name = 'Users';
/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array();
/**
 * Components
 *
 * @var array
 */
	public $components = array( 'Users.kcaptcha');

/**
 * $paginate
 *
 * @var array $paginate
 */
	public $paginate = array('limit' => 5);
	
/**
 * beforeFilter callback
 *
 * @return void
 */	
  public function beforeFilter() {
  			//default title
  			$this->set('title_for_layout', __('Users data',true) );

  			parent::beforeFilter();
        $this->Auth->allow(  
        										'reg','logout','kcaptcha', 'reset_password', 'userNameCheck'
        										//'index','view'
        										//'acoset','aroset','permset','buildAcl'
        										);         
        $this->Auth->autoRedirect = false;
				$this->Auth->loginError ="test mess";
			
				$this->set('model', $this->modelClass);
 
		 		if( $this->action == 'login' && !empty($this->data) ) {
		 				$data = $this->data;	
		       	if( isset($data['User']['username']) && strpos($data['User']['username'],'@')!== false ){	       		
		       		$user = $this->User->find('first',array( 'conditions'=> array('User.email' => $data['User']['username']), 'contain' => false ) );
		       		if($user != array() ){
		       			$this->data['User']['username'] = $user['User']['username'];
		       		}
						}
						
						
						if($this->referer() === '/' || $this->referer() === 'cards/index'){
 
							if (!isset($data['_Token']) || !isset($data['_Token']['fields']) || !isset($data['_Token']['key'])) {
						 		return false;
						 	}
						 	$token = $data['_Token']['key'];					 	 
						 	if ($this->Session->check('_Token')) {
								$tokenData = unserialize($this->Session->read('_Token'));						 	 
						 		if ($tokenData['expires'] < time() || $tokenData['key'] !== $token) {
						 			return false;
						 		}
							}										
						}								
				}       


				
				
        // swiching off Security component for ajax call				
				if( $this->RequestHandler->isAjax() && $this->action == 'userNameCheck' ) { 
		   			$this->Security->validatePost = false;
		   	}
		   	
		   	

 	}

/**
 * ajax staff
 *
 */	

	public function userNameCheck() {

			$contents = array();
			$token = '';
			$type = '';
			$errors = array();
			$toCheck = '';
			
			Configure::write('debug', 0);
			$this->autoLayout = false;
			$this->autoRender = false;
			
			if ( $this->RequestHandler->isAjax() ){

				if (strpos(env('HTTP_REFERER'), trim(env('HTTP_HOST'), '/')) === false) {
					$this->Security->blackHole($this, 'Invalid referrer detected for this request!');
				}


				if( !isset($this->data['_Token']['key']) || ( $this->data['_Token']['key'] !== $this->params['_Token']['key'] )  ) {
					$this->Security->blackHole($this, 'Invalid referrer detected for this request!');
				}
				
				
			
				//don't foreget about santization and trimm
				if( isset($this->data['User']['username']) && $this->data['User']['username'] != null ) {
					$type = 'username';
				} else if( isset($this->data['User']['email']) && $this->data['User']['email'] != null ) {
					$type = 'email';
				} else {
					$this->Security->blackHole($this, 'Invalid referrer detected for this request!');
				}
				
				

				$this->User->set( $this->data );
						

				$errors = $this->User->invalidFields();

										
				if( !isset($errors[$type]) ) {
						$contents['stat'] = 1;							
				} else {
						$contents['stat'] = 0;
						$contents['error'] = $errors[$type];

						if( $type === 'username' && isset($errors[$type]['stopWords']) ) {
							$contents['stW'] = $this->_stopWordsCheck( $this->data['User']['username'] );
						} 
				}

	      $contents = json_encode($contents);
				$this->header('Content-Type: application/json');				
				return ($contents);			
			
			
			
			} else {				
				$this->Security->blackHoleCallback = '_gotov';	
				$this->Security->blackHole($this, 'You are not authorized to process this request!');			
			}
		
	}

/**
 * blackhole redirection
 *
 * @return void
 */	
	private function _gotov() {
					$this->redirect(null, 404, true);
	}	

/**
 * stopWords checking
 *
 * @return void
 */
	private function _stopWordsCheck($username = null ) {					
					$toCheck = strtolower($username);				
					if ( $a = Configure::read('stopWords') ){					
						foreach( $a as $k => $v ) {
							$res = str_replace($v, "", $toCheck ); 
							if( $res !== $toCheck ){
								return $v;
							}
						}
					}
					return false;			
	}
				

/**
 * kcaptcha stuff
 *
 * @return void
 */
	public function kcaptcha() {
  	$this->kcaptcha->render(); 
  } 


/**
 * User register action
 *
 * @return void
 */
	public function reg() {
		
		$this->set('title_for_layout', __('SignUp',true) );
		$this->set('menuType', 'reg');
		
		
		if ($this->Auth->user()) {
			$this->Session->setFlash(__d('users', 'You are already registered and logged in!', true));
			$this->redirect('/');
		}
	
		$stopWord = '';
	
		if ( !empty($this->data) ) {
			//prepering data from kcaptch component to check.			
			$this->data['User']['captcha2'] = $this->Session->read('captcha');

			$user = $this->User->register($this->data);
			
			if ( $user !== false ) {											
				$a = $this->User->read();
				$this->Auth->login($a);
				$this->Session->setFlash(__d('users', 'Your account has been created. You should receive an e-mail shortly to authenticate your account.', true),'default', array('class' => 'flok'));
				$this->redirect('/',null,true);
      } else {
      	
      	$errors = $this->User->invalidFields();
				if( isset( $errors['username']['stopWords'] ) ) {
					$stopWord = $this->_stopWordsCheck( $this->data['User']['username'] );
					$this->set( 'stopWord', $stopWord );
				}
				
				unset($this->data['User']['captcha']);
				$this->Session->setFlash(__d('users','New user\'s accout hasn\'t been created',true) , 'default', array('class' => 'fler') );
			}
		}
		

	}	

/**
 * Common login action
 *
 * @return void
 */


	public function login() {
				
		$user = array();
		$this->set('title_for_layout', __('Login',true) );
		$this->set('menuType', 'login');

		if ($this->Auth->user()) {
			//debug($this->Auth->user('id'));
			$this->User->id = $this->Auth->user('id');
			$this->User->saveField('last_login', date('Y-m-d H:i:s'));

			if ($this->here == $this->Auth->loginRedirect) {
				$this->Auth->loginRedirect = '/';
			}

			$this->Session->setFlash(sprintf(__d('users', '%s you u have successfully logged in', true), $this->Auth->user('username')));
			if (!empty($this->data)) {
				$data = $this->data[$this->modelClass];
			}

			if (empty($data['return_to'])) {
				$data['return_to'] = null;
			}
			$this->redirect($this->Auth->redirect($data['return_to']));
		}

		if (isset($this->params['named']['return_to'])) {
			$this->set('return_to', urldecode($this->params['named']['return_to']));
		} else {
			$this->set('return_to', false);
		}
		
		
		if( !empty($this->data) ) {
			if( !$this->Auth->login($this->data) ) {
				$this->data['User']['password'] = null;
				$this->Session->setFlash(__d('users','Check your login and password',true),'default', array('class' => 'fler'));
			}			
		}
		
	}

/**
 * Common logout action
 *
 * @return void
 */	
	public function logout() {    	    	
    		$tempUserName = sprintf(__d('users', 'Good bay, %s',true),$this->Auth->user('username')); 			
        $this->Auth->logout();
        $this->Session->setFlash( $tempUserName, 'default', array('class' => 'flok') );
        $this->redirect('/');        
  }	

/**
 * Reset Password Action
 *
 * Handles the trigger of the reset, also takes the token, validates it and let the user enter
 * a new password.
 *
 * @param string $token Token
 * @param string $user User Data
 * @return void
 */
	public function reset_password($token = null, $user = null) {
		if (empty($token)) {
			$admin = false;
			if ($user) {
				$this->data = $user;
				$admin = true;
			}
			$this->_sendPasswordReset($admin);
		} else {
			$this->__resetPassword($token);
		}
	}
/**
 * Checks if the email is in the system and authenticated, if yes create the token
 * save it and send the user an email
 *
 * @param boolean $admin Admin boolean
 * @param array $options Options
 * @return void
 */
	protected function _sendPasswordReset($admin = null, $options = array()) {
		$defaults = array(
			'from' => 'noreply@' . env('HTTP_HOST'),
			'subject' => __d('users', 'Password Reset', true),
			'template' => 'password_reset_request');

		$options = array_merge($defaults, $options);

		if (!empty($this->data)) {
			$user = $this->User->passwordReset($this->data);
			if (!empty($user)) {
				$this->set('token', $user[$this->modelClass]['password_token']);
				$this->Email->to = $user[$this->modelClass]['email'];
				$this->Email->from = $options['from'];
				$this->Email->subject = $options['subject'];
				$this->Email->template = $options['template'];

			 /* SMTP Options */
			 
			   $this->Email->smtpOptions = array(
			        'port'=>'25',
			        'timeout'=>'30',
			        'host' => 'r1',
			        'username'=>'akv',
			        'password'=>'Qaz1234'
			   );     	
        $this->Email->delivery = 'smtp';        
    		$this->set('smtp-errors', $this->Email->smtpError);              
       	//$this->Email->delivery = 'debug';
				$this->Email->send();
				
				if ($admin) {
					$this->Session->setFlash(sprintf(__d('users', '%s has been sent an email with instruction to reset their password.', true),$user[$this->modelClass]['email']));
					$this->redirect(array('action' => 'index', 'admin' => true));
				} else {
					$this->Session->setFlash(__d('users', 'You should receive an email with further instructions shortly', true));
					$this->redirect(array('action' => 'login'));
				}
			} else {
				$this->Session->setFlash(__d('users', 'No user was found with that email.', true));
				$this->redirect('/');
			}
		}
		$this->render('request_password_change');
	}
/**
 * This method allows the user to change his password if the reset token is correct
 *
 * @param string $token Token
 * @return void
 */
	private function __resetPassword($token) {
		$user = $this->User->checkPasswordToken($token);
		if (empty($user)) {
			$this->Session->setFlash(__d('users', 'Invalid password reset token, try again.', true));
			$this->redirect(array('action' => 'reset_password'));
		}

		if (!empty($this->data)) {
			if ($this->User->resetPassword(Set::merge($user, $this->data))) {
				$this->Session->setFlash(__d('users', 'Password changed, you can now login with your new password.', true));
				$this->redirect($this->Auth->loginAction);
			}
		}

		$this->set('token', $token);
	}	
	
	
	    
//--------------------------------------------------------------------	



/**
 * user management part.
 *
 * Not done yet.
 */	

	function index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

	function view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

	function add() {
		if (!empty($this->data)) {
			$this->User->create();
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
	}

	function edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->User->read(null, $id);
		}
	}

	function delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for user', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->User->delete($id)) {
			$this->Session->setFlash(__('User deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
	function admin_index() {
		$this->User->recursive = 0;
		$this->set('users', $this->paginate());
	}

	function admin_view($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		$this->set('user', $this->User->read(null, $id));
	}

	function admin_add() {
		if (!empty($this->data)) {
			$this->User->create();
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
	}

	function admin_edit($id = null) {
		if (!$id && empty($this->data)) {
			$this->Session->setFlash(__('Invalid user', true));
			$this->redirect(array('action' => 'index'));
		}
		if (!empty($this->data)) {
			if ($this->User->save($this->data)) {
				$this->Session->setFlash(__('The user has been saved', true));
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(__('The user could not be saved. Please, try again.', true));
			}
		}
		if (empty($this->data)) {
			$this->data = $this->User->read(null, $id);
		}
	}

	function admin_delete($id = null) {
		if (!$id) {
			$this->Session->setFlash(__('Invalid id for user', true));
			$this->redirect(array('action'=>'index'));
		}
		if ($this->User->delete($id)) {
			$this->Session->setFlash(__('User deleted', true));
			$this->redirect(array('action'=>'index'));
		}
		$this->Session->setFlash(__('User was not deleted', true));
		$this->redirect(array('action' => 'index'));
	}
}
?>
