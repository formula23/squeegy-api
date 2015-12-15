<?php namespace App\Console\Commands;

use App\Services\Registrar;
use App\User;
use Bican\Roles\Models\Role;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CreateUser extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'user:create';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Create a user account';

	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        $reg = new Registrar();

        $data = [
            'name'=>$this->argument('name'),
            'email'=>$this->argument('email'),
            'password'=>$this->argument('password'),
            'phone'=>$this->argument('phone'),
        ];

        $validator = $reg->validator($data);

        if($validator->fails()) {
            foreach($validator->errors()->getMessages() as $err) {
                $this->error(implode(", ", $err));
            }
            return;
        }

        $new_user = $reg->create($data);

        if($new_user) {
            $role = Role::where('slug', $this->option('user_type'))->first();
            $new_user->attachRole($role->id);

            $zone_location = [
                '1' => ['latitude'=>'34.041868', 'longitude'=>'-118.425181'],
                '2' => ['latitude'=>'33.87313753', 'longitude'=>'-118.35977216'],
            ];

            $new_user->default_location()->create($zone_location[$this->option('zone')]);
            $new_user->zones()->attach($this->option('zone'));
        }

        $this->info("User created. ID# ".$new_user->id);
        return;
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
			['name', InputArgument::REQUIRED, 'User name'],
			['email', InputArgument::REQUIRED, 'User email address'],
			['password', InputArgument::REQUIRED, 'User password'],
			['phone', InputArgument::OPTIONAL, 'User phone number: 0000000000'],

		];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
			['user_type', null, InputOption::VALUE_OPTIONAL, 'worker OR customer', 'worker'],
			['zone', null, InputOption::VALUE_OPTIONAL, '1 OR 2', '1'],
		];
	}

}
