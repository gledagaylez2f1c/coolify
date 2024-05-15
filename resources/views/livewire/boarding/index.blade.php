@php use App\Enums\ProxyTypes; @endphp
<section class="flex flex-col h-full lg:items-center lg:justify-center">
    <div
        class="flex flex-col items-center justify-center p-10 mx-2 mt-10 bg-white border rounded-lg shadow lg:p-20 dark:bg-transparent dark:border-none max-w-7xl ">
        @if ($currentState === 'welcome')
            <h1 class="text-3xl font-bold lg:text-5xl">Welcome to Coolify</h1>
            <div class="py-6 text-center lg:text-xl">Let me help you set up the basics.</div>
            <div class="flex justify-center ">
                <x-forms.button class="justify-center w-64 box-boarding"
                    wire:click="$set('currentState','explanation')">Get
                    Started
                </x-forms.button>
            </div>
        @elseif ($currentState === 'explanation')
            <x-boarding-step title="What is Coolify?">
                <x-slot:question>
                    Coolify is an all-in-one application to automate tasks on your servers, deploy application with
                    Git
                    integrations, deploy databases and services, monitor these resources with notifications and
                    alerts
                    without vendor lock-in
                    and <a href="https://coolify.io" class="dark:text-white hover:underline">much much more</a>.
                    <br><br>
                    <span class="text-xl">
                        <x-highlighted text="Self-hosting with superpowers!" /></span>
                </x-slot:question>
                <x-slot:explanation>
                    <p><x-highlighted text="Task automation:" /> You don't need to manage your servers anymore.
                        Coolify does
                        it for you.</p>
                    <p><x-highlighted text="No vendor lock-in:" /> All configurations are stored on your servers, so
                        everything works without a connection to Coolify (except integrations and automations).</p>
                    <p><x-highlighted text="Monitoring:" />You can get notified on your favourite platforms
                        (Discord,
                        Telegram, Email, etc.) when something goes wrong, or an action is needed from your side.</p>
                </x-slot:explanation>
                <x-slot:actions>
                    <x-forms.button class="justify-center lg:w-64 box-boarding" wire:click="explanation">Next
                    </x-forms.button>
                </x-slot:actions>
            </x-boarding-step>
        @elseif ($currentState === 'select-server-type')
            <x-boarding-step title="Server">
                <x-slot:question>
                    Do you want to deploy your resources to your <x-highlighted text="Localhost" />
                    or to a <x-highlighted text="Remote Server" />?
                </x-slot:question>
                <x-slot:actions>
                    <x-forms.button class="justify-center w-64 box-boarding" wire:target="setServerType('localhost')"
                        wire:click="setServerType('localhost')">Localhost
                    </x-forms.button>

                    <x-forms.button class="justify-center w-64 box-boarding " wire:target="setServerType('remote')"
                        wire:click="setServerType('remote')">Remote Server
                    </x-forms.button>
                    @if (!$serverReachable)
                        Localhost is not reachable with the following public key.
                        <br /> <br />
                        Please make sure you have the correct public key in your ~/.ssh/authorized_keys file for
                        user
                        'root' and that ssh server is installed and running, or skip the boarding process and add a new private key manually to Coolify and to the
                        server.
                        <br />
                        Check this <a target="_blank" class="underline"
                            href="https://coolify.io/docs/knowledge-base/server/openssh">documentation</a> for further
                        help.
                        <x-forms.input readonly id="serverPublicKey"></x-forms.input>
                        <x-forms.button class="lg:w-64 box-boarding" wire:target="setServerType('localhost')"
                            wire:click="setServerType('localhost')">Check again
                        </x-forms.button>
                    @endif
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Servers are the main building blocks, as they will host your applications, databases,
                        services, called resources. Any CPU intensive process will use the server's CPU where you
                        are deploying your resources.</p>
                    <p><x-highlighted text="Localhost" /> is the server where Coolify is running on. It is not
                        recommended to use one server
                        for everything.</p>
                    <p><x-highlighted text="A remote server" /> is a server reachable through SSH. It can be hosted
                        at home, or from any cloud
                        provider.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'private-key')
            <x-boarding-step title="SSH Key">
                <x-slot:question>
                    Do you have your own SSH Private Key?
                </x-slot:question>
                <x-slot:actions>
                    <x-forms.button class="justify-center lg:w-64 box-boarding" wire:target="setPrivateKey('own')"
                        wire:click="setPrivateKey('own')">Yes
                    </x-forms.button>
                    <x-forms.button class="justify-center lg:w-64 box-boarding" wire:target="setPrivateKey('create')"
                        wire:click="setPrivateKey('create')">No (create one for me)
                    </x-forms.button>
                    @if (count($privateKeys) > 0)
                        <form wire:submit='selectExistingPrivateKey' class="flex flex-col w-full gap-4 lg:pr-10">
                            <x-forms.select label="Existing SSH Keys" id='selectedExistingPrivateKey'>
                                @foreach ($privateKeys as $privateKey)
                                    <option wire:key="{{ $loop->index }}" value="{{ $privateKey->id }}">
                                        {{ $privateKey->name }}</option>
                                @endforeach
                            </x-forms.select>
                            <x-forms.button type="submit">Use this SSH Key</x-forms.button>
                        </form>
                    @endif
                </x-slot:actions>
                <x-slot:explanation>
                    <p>SSH Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                    <p>You can use your own ssh private key, or you can let Coolify to create one for you.</p>
                    <p>In both ways, you need to add the public version of your ssh private key to the remote
                        server's
                        <code class="dark:text-warning">~/.ssh/authorized_keys</code> file.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'select-existing-server')
            <x-boarding-step title="Select a server">
                <x-slot:question>
                    There are already servers available for your Team. Do you want to use one of them?
                </x-slot:question>
                <x-slot:actions>
                    <div class="flex flex-col gap-4">
                        <div>
                            <x-forms.button class="justify-center w-64 box-boarding" wire:click="createNewServer">No
                                (create
                                one
                                for
                                me)
                            </x-forms.button>
                        </div>
                        <div>
                            <form wire:submit='selectExistingServer' class="flex flex-col w-full gap-4 lg:w-96">
                                <x-forms.select label="Existing servers" class="w-96" id='selectedExistingServer'>
                                    @foreach ($servers as $server)
                                        <option wire:key="{{ $loop->index }}" value="{{ $server->id }}">
                                            {{ $server->name }}</option>
                                    @endforeach
                                </x-forms.select>
                                <x-forms.button type="submit">Use this Server</x-forms.button>
                            </form>
                        </div>
                    </div>
                    @if (!$serverReachable)
                        This server is not reachable with the following public key.
                        <br /> <br />
                        Please make sure you have the correct public key in your ~/.ssh/authorized_keys file for
                        user
                        'root' or skip the boarding process and add a new private key manually to Coolify and to the
                        server.
                        <x-forms.input readonly id="serverPublicKey"></x-forms.input>
                        <x-forms.button class="w-64 box-boarding" wire:target="validateServer"
                            wire:click="validateServer">Check
                            again
                        </x-forms.button>
                    @endif
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Private Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                    <p>You can use your own private key, or you can let Coolify to create one for you.</p>
                    <p>In both ways, you need to add the public version of your private key to the remote server's
                        <code>~/.ssh/authorized_keys</code> file.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-private-key')
            <x-boarding-step title="Create Private Key">
                <x-slot:question>
                    Please let me know your key details.
                </x-slot:question>
                <x-slot:actions>
                    <form wire:submit='savePrivateKey' class="flex flex-col w-full gap-4 lg:pr-10">
                        <x-forms.input required placeholder="Choose a name for your Private Key. Could be anything."
                            label="Name" id="privateKeyName" />
                        <x-forms.input placeholder="Description, so others will know more about this."
                            label="Description" id="privateKeyDescription" />
                        <x-forms.textarea required placeholder="-----BEGIN OPENSSH PRIVATE KEY-----" label="Private Key"
                            id="privateKey" />
                        @if ($privateKeyType === 'create')
                            <x-forms.textarea rows="7" readonly label="Public Key" id="publicKey" />
                            <span class="font-bold dark:text-warning">ACTION REQUIRED: Copy the 'Public Key' to your
                                server's
                                ~/.ssh/authorized_keys
                                file.</span>
                        @endif
                        <x-forms.button type="submit">Save</x-forms.button>
                    </form>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Private Keys are used to connect to a remote server through a secure shell, called SSH.</p>
                    <p>You can use your own private key, or you can let Coolify to create one for you.</p>
                    <p>In both ways, you need to add the public version of your private key to the remote server's
                        <code>~/.ssh/authorized_keys</code> file.
                    </p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-server')
            <x-boarding-step title="Create Server">
                <x-slot:question>
                    Please let me know your server details.
                </x-slot:question>
                <x-slot:actions>
                    <form wire:submit='saveServer' class="flex flex-col w-full gap-4 lg:pr-10">
                        <div class="flex flex-col gap-2 lg:flex-row">
                            <x-forms.input required placeholder="Choose a name for your Server. Could be anything."
                                label="Name" id="remoteServerName" />
                            <x-forms.input placeholder="Description, so others will know more about this."
                                label="Description" id="remoteServerDescription" />
                        </div>
                        <div class="flex flex-col gap-2 lg:flex-row ">
                            <x-forms.input required placeholder="127.0.0.1" label="IP Address" id="remoteServerHost" />
                            <x-forms.input required placeholder="Port number of your server. Default is 22."
                                label="Port" id="remoteServerPort" />
                            <div class="w-full">
                                <x-forms.input required placeholder="User to connect to your server. Default is root."
                                    label="User" id="remoteServerUser" />
                                <div class="text-xs dark:text-warning text-coollabs ">Non-root user is experimental: <a
                                        class="font-bold underline" target="_blank"
                                        href="https://coolify.io/docs/knowledge-base/server/non-root-user">docs</a>.
                                </div>
                            </div>
                        </div>
                        <div class="lg:w-64">
                            <x-forms.checkbox
                                helper="If you are using Cloudflare Tunnels, enable this. It will proxy all ssh requests to your server through Cloudflare.<br><span class='dark:text-warning'>Coolify does not install/setup Cloudflare (cloudflared) on your server.</span>"
                                id="isCloudflareTunnel" label="Cloudflare Tunnel" />
                        </div>
                        <x-forms.button type="submit">Continue</x-forms.button>
                    </form>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Username should be <x-highlighted text="root" /> for now. We are working on to use
                        non-root users.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'validate-server')
            <x-boarding-step title="Validate & Configure Server">
                <x-slot:question>
                    I need to validate your server (connection, Docker Engine, etc) and configure if something is
                    missing for me. Are you okay with this?
                </x-slot:question>
                <x-slot:actions>
                    <x-slide-over closeWithX fullScreen>
                        <x-slot:title>Validate & configure</x-slot:title>
                        <x-slot:content>
                            <livewire:server.validate-and-install :server="$this->createdServer" />
                        </x-slot:content>
                        <x-forms.button @click="slideOverOpen=true" class="w-full font-bold box-boarding lg:w-96"
                            wire:click.prevent='installServer' isHighlighted>
                            Let's do it!
                        </x-forms.button>
                    </x-slide-over>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>This will install the latest Docker Engine on your server, configure a few things to be able
                        to run optimal.<br><br>Minimum Docker Engine version is: 22<br><br>To manually install
                        Docker
                        Engine, check <a target="_blank" class="underline dark:text-warning"
                            href="https://docs.docker.com/engine/install/#server">this
                            documentation</a>.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-project')
            <x-boarding-step title="Project">
                <x-slot:question>
                    @if (count($projects) > 0)
                        You already have some projects. Do you want to use one of them or should I create a new one
                        for
                        you?
                    @else
                        Let's create an initial project for you. You can change all the details later on.
                    @endif
                </x-slot:question>
                <x-slot:actions>
                    <x-forms.button class="justify-center w-64 box-boarding" wire:click="createNewProject">Create new
                        project!</x-forms.button>
                    <div>
                        @if (count($projects) > 0)
                            <form wire:submit='selectExistingProject' class="flex flex-col w-full gap-4 lg:w-96">
                                <x-forms.select label="Existing projects" class="w-96" id='selectedProject'>
                                    @foreach ($projects as $project)
                                        <option wire:key="{{ $loop->index }}" value="{{ $project->id }}">
                                            {{ $project->name }}</option>
                                    @endforeach
                                </x-forms.select>
                                <x-forms.button type="submit">Use this Project</x-forms.button>
                            </form>
                        @endif
                    </div>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>Projects contain several resources combined into one virtual group. There are no
                        limitations on the number of projects you can add.</p>
                    <p>Each project should have at least one environment, this allows you to create a production &
                        staging version of the same application, but grouped separately.</p>
                </x-slot:explanation>
            </x-boarding-step>
        @elseif ($currentState === 'create-resource')
            <x-boarding-step title="Resources">
                <x-slot:question>
                    Let's go to the new resource page, where you can create your first resource.
                </x-slot:question>
                <x-slot:actions>
                    <div class="items-center justify-center w-64 box-boarding" wire:click="showNewResource">Let's do
                        it!</div>
                </x-slot:actions>
                <x-slot:explanation>
                    <p>A resource could be an application, a database or a service (like WordPress).</p>
                </x-slot:explanation>
            </x-boarding-step>
        @endif
    </div>
    <div class="flex flex-col justify-center gap-4 pt-4 lg:gap-2 lg:flex">
        <div class="flex justify-center w-full gap-2">
            <div class="cursor-pointer hover:underline dark:hover:text-white" wire:click='skipBoarding'>Skip
                onboarding</div>
            <div class="cursor-pointer hover:underline dark:hover:text-white" wire:click='restartBoarding'>Restart
                onboarding</div>
        </div>
        <x-modal-input title="How can we help?">
            <x-slot:content>
                <div class="w-full text-center cursor-pointer hover:underline dark:hover:text-white"
                    title="Send us feedback or get help!">
                    Feedback
                </div>
            </x-slot:content>
            <livewire:help />
        </x-modal-input>
    </div>
    </div>
</section>
