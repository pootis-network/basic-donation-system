
> This was the original readme that came with the script. I was still in the process of learning English, so please don't judge my grammar too much :).

---
# Donation System Readme
## About the script
DonationSystem by [H3xCat](https://steamcommunity.com/profiles/76561198000622892) allows server owners to have automated donation services in their servers.

## config.php syntax
The config.php file have php syntax and you must follow them while modifying the file. The file mostly consist of some variables and multi-dimensional arrays.

**When writing inside array, each value inside array (including inner arrays such as 'execute' array in packages) must be separated by comma.**

I also recommend using Notepad++ and use spacing as I do in examples bellow, it will be easier to track where arrays start and end.

## Setup: Web Server
All modifications should be done in config.php file.

1. Extract all files and folders from "web" folder to "donate" folder(create one if needed) in your website( example.com/donate/ )
2. Edit config.cfg.
    ### Database
    
    | Variable | Description |
    |---|---|
    | DB_HOST | MySQL Server IP Address. If you don't know what to put, then nether do I, please contact your webhost admin for that info. |
    | DB_USERNAME | MySQL Server Username |
    | DB_PASSWORD | MySQL Server Password |
    | DB_DATABASE | MySQL Server Database |
    
    ### Paypal
        
    | Variable | Description |
    |---|---|
    | PAYPAL_CURRENCY | Currency that you're going to use on the system (default USD). |
    | PAYPAL_SANDBOX | Set to true to test your donation system with paypal sandbox account. When done testing set it to false. |
    | PAYPAL_ID | Put your current paypal id(email). |
    | PAYPAL_ID_SANDBOX | Put sandbox paypal id(email). It is used to test the system without spending real money. To create sandbox accounts you have to go here. You must create two accounts, one is seller other is buyer. |
    
    ### Steam API
    
    | Variable | Description |
    |---|---|
    | STEAM_API | Go here to generate your Steam API Key(use domain name where the donation system is located such as "example.com"). |
    
    ### Donation System URL
    
    | Variable | Description |
    |---|---|
    | DONATE_URL | Write address to the donation system ("http://example.com/donate/"). Don't forget to include http:// and don't include file such as index.php, it must be folder. If you use SSL protocol, use `https://`. |
    
    ### OpenID
    
    | Variable | Description |
    |---|---|
    | OPENID_MODE | You should leave this alone. Only try switching if steam sign in doesn't work. If nether 'curl' or 'streams' work, contact your web host admin to fix curl.|
    
    ### Games
    Default config file is configured for one game.
    If you wish to add new game you can copy "gmod" array and paste new array and change GameId. Each server must be separated by comma.
    
    ![](https://i.imgur.com/MA1T3jt.png)
    
    Each game must have their own GameId, you can give any valid id you want but they must be unique. In order for GameID to be valid it must be lower case, no special symbols, and no spaces.
    
    Inside array you should find four values.
    
    | Variable | Description |
    |---|---|
    | name | Title of your game. |
    | icon | Icon that is shown next to title. |
    | display | If this set to false it won't show up in Games section. |
    | servers | List of servers that is shown for this specific game. You must use ServerId. When adding, write inside array. |
    
    ### Servers
    
    Default config file is configured for two server("darkrp" and "ttt"). If you wish to add new game you can copy "darkrp" or "ttt" array and paste new array and change ServerId. Each server must be separated by comma.
    
    ![](https://i.imgur.com/OTvxkJ2.png)
    
    Each server must have their own ServerId, you can give any id you want but they must be unique and valid same as in Games array for GameID.
    
    Inside array you should find four values.
    
    | Variable | Description |
    |---|---|
    | name | Title of your server. |
    | icon | Icon that is shown next to title. |
    | packages | List of packages that is shown for this specific server. You must use PackageId. When adding, write inside array. |
    
    ### Packages
    
    In default config file there is already six packages array for darkrp and ttt servers, feel free to change modify if needed.

    Package id's must be numbers only. If you wish to add new package you can copy first package and paste new and change PackageId. Don't forget to separate packages with comma. Each package must have their own PackageId that consist only whole numbers, you can give any id you want but they must be unique.

    Inside array you should find five values.
    
    | Variable | Description |
    |---|---|
    | title | This title is shown when you're in Packages section. You can also apply html tags if you want.
    | buytitle | This title is shown to user who's in order.php or paypal checkout website.
    | description | This is where you write your package description. I recommend using html tags when writing descriptions.
    | price | Package price. Number only, no currency symbols.
    | execute | Commands to be executed after purchase. Please reffer to **execute** section for more info |
    | checkout | File where checkout is processed, you should leave this to "source.php". |
    | paypal_id | (Optional) Specify to what paypal account should money be sent. If nothing specified, it will use PAYPAL_ID instead. `'paypal_id' => "email@example.com"` |

    ### Packages: execute
    This is probably most important part of the donation system. This is where you specify what and when you want to execute certain commands when someone donates. In execute array it's where you define in what servers the commands are going to be executed. Inside those server arrays there are other 2 arrays
    
    | Variable | Description |
    |---|---|
    | online | When you add commands to online array, it's going to activate commands once player is connected to database. It's recommended to add commands to online array because more commands are supported in this array like pointshop_points and darkrp_money
    | offline | When you add commands to offline array, it's going to activate commands instantly(unless delay is other than 0) after someone donates no matter if player is on server or not.
    
    Inside online/offline array you should notice there are arrays that have numbers in front of them, those numbers are delays when array of commands is going to be executed after certain amount of seconds. Put 0 if you want instant. Inside those arrays you specify what commands you want to run, please go to "Execute Commands" section of readme file for the list of commands. 

    You can add and remove arrays inside offline/online arrays.
    It could be:
    ```
    "online" => array() //You're not required to include online array at all if its empty.
    ```
    or
    ```
    "online" => array(
        0 => array(
            ...
        )
    )
    ```
    or
    ```
    "online" => array(
        0 => array(
            ...
        ),
        86400*30 => array(
            ...
        )
    )
    ```
    or
    ```
    "online" => array(
        0 => array(
            ...
        ),
        86400*30 => array(
            ...
        ),
        86400*60 => array(
            ...
        )
    )
    ```
    ![](https://i.imgur.com/tY2nRgr.png)
3. After editing config.php, edit tos.html to add your own Terms of Service.

## Setup: Game Server
1. Extract donation_system.lua to "garrysmod/lua/autorun/server/" folder.
2. Open donation_system.lua and fill folowing values.

    | Variable | Description |
    |---|---|
    | DB_HOST | MySQL Server IP Address. If you don't know what to put, then nether do I, please contact your webhost admin for that info. |
    | DB_USERNAME | MySQL Server Username. |
    | DB_PASSWORD | MySQL Server Password. |
    | DB_DATABASE | MySQL Server Database. *|
    | SERVER_ID | This should match ServerID that you specified in config.php for server. |
    
    \* *Make sure external connection is allowed for this database. If the system fails to connect and you're sure information is correct, contact webhost admin to resolve this.*
    
    ### SERVER_ID relation between config files
    ![](https://i.imgur.com/st8XBp7.png)
    
    *Don't mind `DONATE_URL` and `LINK_AUTH_KEY` variables in picture as they were removed in 4.0*

3. This DonationSystem requires MySQLOO v8 module to be installed on the server. To install it you have to extract gmsv_mysqloo_win32.dll/gmsv_mysqloo_linux.dll(you can add both if you want) to "garrysmod/lua/bin/" folder(create folder if folder doesn't exist). You also need to extract libmysql.dll/libmysql.so.18 to directory where sever executable is located (often called srcds.exe). Some game hosting services would rename executable or hide it from you, if that's your case then you would need to contact them and get assistance installing libmysql file.

## Execute Commands

### Commands

| Command | Description | Example | Offline |
|---|---|---|---|
| darkrp_money | Gives user certain amount of darkrp money | `array("darkrp_money", 1000)` | No |
| pointshop_points | Gives user certain amount of pointshop points | `array("pointshop_points", 1000)` | No |
| print | Prints message to the buyer, you can set colours of text by adding array(r,g,b) to the array and you are also able to add multiple of them | `array("print", array(255,0,0) , "Thank you for donating!" )` | No |
| broadcast | Prints message to everyone in server, you can set colours of text by adding array(r,g,b) to the array and you are also able to add multiple of them | `array("print", array(255,0,0), "%name% have donated!" )` | Yes |
| broadcast_omit | Prints message to everyone in server except the buyer, you can set colours of text by adding array(r,g,b) to the array and you are also able to add multiple of them | `array( "print",array(255,0,0), "%name% have donated!" )` | Yes |
| lua | Runs lua in server, use PLAYER(Entity) or STEAMID(String) to apply to buyer. | Please reffer to **Command: lua** | Yes |
| sv_cmd | Runs console command in server | `array( "sv_cmd", "say", "%name% donated!" )` | Yes |
| cl_cmd | Runs console command for buyer | `array( "cl_cmd", "say", "I donated!" )` | No |
| cancel | Cancels past orders(with delays) from executing. Reffer to **Commands: cancel** for more info. | Reffer to **Commands: cancel** | Yes |
| gforum_smf_usergroup | Updates smf group trough gForum, you have to use group id | `array( "gforum_smf_usergroup", 1)` | Yes |

### Command: lua
#### Example
```php
array("lua","
  if PLAYER:IsUserGroup(\"vip\") then
      ULib.ucl.addUser(STEAMID,{},{},\"vip-donator\")
  elseif PLAYER:IsUserGroup(\"admin\") then
      ULib.ucl.addUser(STEAMID,{},{},\"admin-donator\")
  elseif PLAYER:IsUserGroup(\"superadmin\") then
      ULib.ucl.addUser(STEAMID,{},{},\"superadmin-donator\")
  else
      ULib.ucl.addUser(STEAMID,{},{},\"donator\")
  end
")
```
**Note:** I use escape symbol (`\`) to escape strings when I write `"` inside string.

### Command: cancel
  Cancels past orders(with delays) from executing. This is most 
  likely to be used with ranks. For example player bought VIP rank
  on your server for a month, after 3 weeks he bought an Admin rank
  before expiring his VIP rank, and he receives his Admin rank. But
  after 1 week his rank is removed because of VIP order(that was
  ordered 4 weeks ago). 

  To prevent past orders from executing we run this command to 
  cancel past orders.

  You can have parameters between 0 and 6.

  | Parameter | Description |
  |---|---|
  | excludeself | Set it to `true` to avoid canceling commands from current order. Most of the time this is set to `true`, only on special occurence you should have it set to `false`. |
  | serverid | ServerID where you want to cancel the commands in. |
  | packageid | Package that you want to cancel. |
  | online | Set it to `true` to cancel `online` commands. `false` to cancel `offline` commands. |
  | delay | Remove commands that have this delay. |
  | commandid | Specific command that you want to cancel. Any number equal or greater than 1. |
  
  ### Variables

  | Variables | Type | Applies to | Description | Offline |
  |---|---|---|---|---|
  | %name% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer name(steam name). | Yes |
  | %gamename% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer in game name (for example in DarkRP gamemode where you can change your names). | No |
  | %name_esc% | text | sql, sql_ext | SQL escaped name. | Yes |
  | %gamename_esc% | text | sql, sql_ext | SQL escaped game name. | No |
  | %steamid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer SteamID.  | Yes |
  | %steamid64% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer CommunityID | No |
  | %uniqueid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer UniqueID. | Yes |
  | %userid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Buyer UserID. | No |
  | %orderid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | The id of current order from where executed. | Yes |
  | %transactionid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Paypal transaction id. | Yes |
  | %packageid% | text | print, broadcast, broadcast_omit, sv_cmd, cl_cmd, sql, sql_ext | Package id. | Yes |
  | PLAYER | entity | lua | Buyer entity. | No |
  | STEAMID | string | lua | Buyer SteamID. | Yes |
  | ORDERDATA | table | lua | Order table. (same structure as in `commands` database, for example `ORDERDATA.transactionid` will give you transactionid) | Yes |

## Troubleshooting

  | Error | File | Cause | Fix |
  |---|---|---|---|
  | PHP Warning:  mysqli_stmt_close(): invalid object or resource mysqli_stmt in .../donate/ipn.php on line 107 | ipn.php (logged in ipn_errors.log) | Table structure doesn't match in database. | Delete "payments" and "commands" tables in database. |
  | Steam sign doesn't work. | index.php | Either curl or streams doesn't work correctly. | Make sure donate url have right protocol, sometimes web with http protocol would redirect to https and if that happens you need to change protocol on donate url to https. If that doesn't work try  switching OPENID_MODE between 'curl' or 'streams'. If nether of that works, contact your webhost admin and ask to fix curl. (most of the time when people do contact their webhost admin about this problem, it does get fixed) |
  | Clicking "Next" button brings to Page Not Found. | index.php | Wrong DONATE_URL format. | Please make sure you use same format as given example. You have to include http:// in front and end it with slash at the end. `http://example.com/donate/` |
  | Checkout button doesn't work | index.php | Domain redirect | Set DONATE_URL to your webhost ip address. If you use port other than 80 you should add port next to ip. e.g. `http://11.22.33.44:8080/donate/`. Also try using without any forwarding systems, and use direct ip address.
  | Payments go trough web and adds data to the database but it doesn't execute on servers. | | | There could be multiple causes |
  | | | Mysqloo module is not installed correctly. | Type following command in server console: `lua_run require("mysqloo")`. If it returns `[ERROR] lua_run:1: Couldn't load module library!` then you should go Game Server Setup section and read step 3. |
  | | | The `serverid` mismatch between configuration files. | Type `ds_printlogs` in server console and if it says that it has connected to the database then it's most likely that serverid in lua file doesn't match with the serverid in the 'execute' array inside PACKAGE (not SERVERS) array. |
  | | | Incorrect database setup. | If `ds_printlogs` returns that it has failed to connect to the server. Read the cause and check if all db info matches. If it's still doesn't work, then contact webhost admin as they might be blocking external access. If you're unable to enable external access on your webhost, then try getting new host such as nfoservers.com. |
  | `[ERROR] lua/autorun/server/donation_system.lua:32: Couldn't load module library! 1. require - [C]:-1 2. unknown - lua/autorun/server/donation_system.lua:32` | donation_system.lua | MySQLOO module is not installed correctly. | Make sure you did game server setup(step 3) correctly. If you're sure you did it correctly, contact your game host provider to resolve the issue. You should include the error when contacting them. |
  | Empty boxes in either Servers or Packages tab | index.php | `config.php` has incorrect setup. | If Servers tab has empty box, go to $GAMES array and inside game array find `servers` array and remove extra server. If Packages tab has empty box, go to $SERVERS array and inside server array find `packages` array and remove extra package. |
  | Error while validating data | success.php | | Open ipn_error.log file in your webhost and if it says: `cURL error: [35] Unsupported SSL protocol version` This means your webhost have a curl version that has a [bug](http://curl.haxx.se/mail/tracker-2014-01/0003.html). You should contact your webhost to resolve this issue. |
  | Sandbox mode works fine, but when I use regular Paypal the system wont register payments. | ipn.php | ipn.php didnt register the payment | Sometimes payments goes pending. it's hard to determine the reason but most of the time its because someone pays you in diferent currency that your paypal is set to. Try setting same currency in config.php that your paypal account use. If it says that transaction went completed try linking ipn messages in your paypal settings to the ipn.php file. Normally you don't need to configure ipn settings, but sometimes paypal won't send ipn messages trough notify_url value. |

