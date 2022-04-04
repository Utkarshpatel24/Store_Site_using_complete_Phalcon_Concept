<?php
use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Controller;
//______FOR JWT_______
use Firebase\JWT\JWT;
use Firebase\JWT\Key;


class UserController extends Controller{

    /**
     * Function for users
     *
     * @return void
     */
    public function indexAction()
    {

        if($this->cookies->has('remember-mebro') || count($this->session->get('mydetails')) != 0 )
        $this->response->redirect('/dashboard');
    }
    /**
     * Function for signup
     *
     * @return void
     */
    public function signupAction()
    {
        $this->view->message= '';
        $postdata=$this->request->getPost();
        if (count($postdata) != 0) {
            
            if ($postdata['password'] == $postdata['password1']) {
                $user=new Users();

                $user->name = $this->escaper->escapeHtml($postdata['name']);
                $user->username = $this->escaper->escapeHtml($postdata['username']);
                $user->email = $this->escaper->escapeHtml($postdata['email']);
                $user->password = $this->escaper->escapeHtml($postdata['password']);
                $user->role = $this->escaper->escapeHtml($postdata['role']);
                $key = "example_key";

                $payload = array(
                    "iss" => "http://example.org",
                    "aud" => "http://example.com",
                    "iat" => 1356999524,
                    "nbf" => 1357000000,
                    'name' => $user->name,
                    'role' => $user->role
                );
                $jwt = JWT::encode($payload, $key, 'HS256');
                $user->token = $jwt;
                $status = 'pending';
                if ($postdata['role'] == 'admin')
                $status = "approved";
                $user->status = $status;
                $result = $user->save();
                if($result)
                 $this->view->message = "Successfully registered!! Now wait for approval";
                else
                 {
                     $this->view->message = "Not registered successfully!! Please try again";
                     $this->signupLog->alert("Please Enter Valid details to Sign-up");
                }

            } else {
                $this->view->message = "Password Miss Matched";
                $this->signupLog->alert("Password Miss Matched");
            }
        }
    }
    /**
     * Function for Login
     *
     * @return void
     */
    public function loginAction()
    {

        $postdata = $this->request->getPost();
                
        if (count($postdata) != 0 ) {

            // print_r($postdata);
            
            $user = Users::find(
                [
                    'conditions' => 'email = ?1 AND password = ?2 ',
                    'bind'       => [
                        1 => $postdata['email'],
                        2 => $postdata['password'],
                        // 3 => 'approved'
                    ]
                ]
            );
          
           

          
            if (count($user) != 0) {


                 $this->session->mydetails = $this->getArray($user[0]);
                if (count($postdata) == 4) {
                    $cookie = $this->cookies;
                    $cookie->set(
                        'remember-mebro',
                        json_encode(
                            [
                                'email' => $this->escaper->escapeHtml($postdata['email']),
                                'password' => $this->escaper->escapeHtml($postdata['password'])
                            ]
                        ),
                        time() + 3600
                    );
                    
                    $this->response->setCookies($cookie);
                    $this->response->send();
                    
                } 
                // echo $user[0]->token;
                // die;
                $this->response->redirect('/dashboard/index/?bearer='.$user[0]->token);
            } else {
                $this->loginLog->alert("Enter correct email and password");
            }
            
        }
    }
    /**
     * Function for Signout
     *
     * @return void
     */
    public function signoutAction()
    {
        $this->session->destroy();
        $this->cookies->get('remember-mebro')->delete();
        $this->response->redirect('/user/login');
    }
    /**
     * Function to edit Profile
     *
     * @return void
     */
    public function editprofileAction()
    {
        
    }

    /**
     * Function to Update Profile
     *
     * @return void
     */
    public function updateprofileAction()
    {
        $postdata = $this->request->getPost();
        $user = Users :: findFirst($this->session->get('mydetails')['user_id']);
        $user->name = $postdata['name'];
        $user->username = $postdata['username'];
        $user->email = $postdata['email'];
        $user->password = $postdata['password'];
        $user->role = $postdata['role'];
        $user->status = $postdata['status'];
        $user->update();
        $this->session->mydetails = $this->getArray($user);
        $this->response->redirect('/dashboard');
        
    
    }
    /**
     * Functoion to change status
     *
     * @return void
     */
    public function changestatusAction()
    {
        $action = $this->request->getQuery('action');
        echo $action;
        // die;
        $user_id = substr($action, 4);
        $action = substr($action, 0, 3);
        $user = Users::findFirst($user_id);
        switch($action)
        {
            case 'app':
                {
                    // echo $action;
                    // die;
                    $user->status = 'approved';
                    $user->update();
                    break;
                }
            case 'dis':
                {
                    $user->status = 'dissapproved';
                    $user->update();
                    break;
                }
            case 'del':
                {
                    $user->delete();
                    break;
                }    

        }

        $this->response->redirect('/dashboard/customerlist');

    }


    /**
     * Function to get array of object
     *
     * @param [type] $user
     * @return void
     */
    public function getArray($user)
    {
        return array(
            'user_id' => $user->user_id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'password' => $user->password,
            'role' => $user->role,
            'status' => $user->status,
            'token' => $user->token
        );
    }
}