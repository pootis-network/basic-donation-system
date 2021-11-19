local DB_HOST = ""
local DB_USERNAME = ""
local DB_PASSWORD = ""
local DB_DATABASE = ""
local SERVER_ID = "darkrp"

local CHAT_CMD_ENABLE = false
local CHAT_CMD_COMMANDS = {"!donate", "/donate"}
local CHAT_CMD_URL = "http://example.com/donate/"

---------------------------------------------------------------
-- Leave everything below, unless you know what you're doing  -
---------------------------------------------------------------

DonationSystem = { }
local DS = DonationSystem

---- Logs -----------------------------------------------------

DS.Logs = { }

local function addlog( str )
	DS.Logs[ #DS.Logs + 1 ] = { os.date( ), str }
	Msg( "[DonationSystem] " .. tostring( str ) .. "\n" )
	file.Append( "donationsystem_logs.txt", os.date( ) .. "\t" .. str .. "\n" )
end
addlog( "Initiating DonationSystem v4.6" )

---- Chat Commands --------------------------------------------

local function donateCommand( pl, text, teamonly )
	if table.HasValue( CHAT_CMD_COMMANDS, text ) then
		pl:SendLua([[gui.OpenURL("]] .. CHAT_CMD_URL .. [[")]])
		return ""
	end
end
if CHAT_CMD_ENABLE then
	hook.Add( "PlayerSay", "DonationSystemChat", donateCommand )
end

---- Console Commands -----------------------------------------------

local function consolePrint( ply, msg )
	if IsValid(ply) then 
		ply:PrintMessage(HUD_PRINTCONSOLE, msg )
	else
		print(msg)
	end
end

concommand.Add( "ds_printlogs",function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return end
	consolePrint( ply, "DonationSystem Logs:" )
	for i=1, #DS.Logs do
		consolePrint( ply, table.concat( DS.Logs[ i ], "\t" ) )
	end
end )

concommand.Add( "ds_debuginfo",function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return end
	consolePrint( ply, "DonationSystem Debug Info:" )
	consolePrint( ply, "HOSTNAME:  (".. #DB_HOST.. ")\t" .. DB_HOST )
	consolePrint( ply, "USERNAME:  (".. #DB_USERNAME.. ")\t" .. DB_USERNAME )
	consolePrint( ply, "PASSWORD:  (".. #DB_PASSWORD.. ")\t" .. string.rep("*",#DB_PASSWORD) )
	consolePrint( ply, "DATABASE:  (".. #DB_DATABASE.. ")\t"  .. DB_DATABASE)
	consolePrint( ply, "SERVER_ID: (".. #SERVER_ID.. ")\t" .. SERVER_ID )
	consolePrint( ply, "MySQLOO:\t" .. (mysqloo and "LOADED" or "NOT LOADED") )
	
end )

concommand.Add( "ds_status", function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return end
	consolePrint( ply, "Database Status: " .. DS.Database:status( ) )
end )

---- MySQLOO --------------------------------------------------
local succ, err = pcall(require, "mysqloo" )
if succ then
	addlog( "Successfully loaded MySQLOO module!" )
else
	addlog( "Failed to load MySQLOO module: " .. err )
	return nil
end

DS.Database = mysqloo.connect( DB_HOST, DB_USERNAME, DB_PASSWORD, DB_DATABASE, 3306 )
local db = DS.Database
local queue = {}
local function query( sql, callback )
	local q = db:query( sql )
	if not q then	
		table.insert( queue, { sql, callback } )
		db:connect( )
		return
	end
	function q:onSuccess( data )
		if type( callback ) == "function" then
			callback( data, q )
		end
	end
	function q:onError( err )
		if db:status() == mysqloo.DATABASE_NOT_CONNECTED then
			table.insert( queue, { sql, callback } )
			db:connect( )
			return
		else
			DS.DatabaseCheck( )
			addlog( "Query Error: " .. err .. " sql: " .. sql )
		end
	end
	q:start()
end

function db:onConnected( )	
	addlog( "Connected to database" )
	DS.DatabaseCheck( )
	for k, v in pairs( queue ) do
		query( v[ 1 ], v[ 2 ] )
	end
	queue = {}
end
 
function db:onConnectionFailed( err )
    addlog( "Connection to database failed! Error: " .. err )
end

db:connect( )

function DS.DatabaseCheck( )
	query( [[
		CREATE TABLE IF NOT EXISTS `commands` (
		id MEDIUMINT NOT NULL AUTO_INCREMENT,
		serverid varchar(40) NOT NULL,
		packageid int(6) unsigned NOT NULL,
		online TINYINT(1) UNSIGNED NOT NULL,
		commandid int(6) unsigned NOT NULL,
		delay INT UNSIGNED NOT NULL,
		activatetime INT UNSIGNED NOT NULL,
		command TEXT NOT NULL,
		activated TINYINT(1) UNSIGNED NOT NULL,
		transactionid varchar(125) NOT NULL,
		playerid varchar(40) NOT NULL,
		playername varchar(40) NOT NULL, 
		PRIMARY KEY (id))
	]] )
end

---- Commands -------------------------------------------------

util.AddNetworkString( "DonationSystemColorChat" )
util.AddNetworkString( "DonationSystemConCommand" )

hook.Add( "PlayerInitialSpawn", "DonationSystemPlayerJoinSendLua", function(ply)
	ply:SendLua( [[ 
		net.Receive( "DonationSystemColorChat", function( len ) chat.AddText( unpack( net.ReadTable() ) ) end ) 
		net.Receive( "DonationSystemCmd", function( len ) RunConsoleCommand( unpack( net.ReadTable() ) ) end ) 
	]] )
end)

DS.Commands = {
	[ "gforum_smf_usergroup" ] = function( data, args, ply )
		if Forum == nil then
			error( "gForum is not installed" )
		end
		local steamid = data.playerid
		local gid = args[1]
		if tonumber( gid ) == nil then
			error( "Invalid first argument. Got " .. type(gid) .. ", expected number.")
			return nil
		end
		if Forum == "smf" then
			local query1 = database:query("SELECT `id` FROM " .. Prefix .. "_link WHERE `steamid`='" .. steamid .. "'")
			query1.onError = function( err, sql )
				addlog( "Error executing gforum_smf_usergroup command.\nQuery1 errored!\nQuery:" .. sql .. "\nError: " .. err )
			end
			query1.onSuccess = function( query, data )
				local id = query:getData()[1]['id'] or nil
				if id then
					local query2 = database:query("SELECT `id_member`, `member_name`, `id_group`, `personal_text` FROM " .. Prefix .. "_members WHERE `id_member`='" .. id .. "'")
					query2.onError = function( err, sql )
						addlog( "Error executing gforum_smf_usergroup command.\nQuery2 errored!\nQuery:" .. sql .. "\nError: " .. err )
					end
					query2.onSuccess = function( _query )
						local Args1 = _query:getData()[1] or nil
						if Args1['id_member'] then
							database:query("UPDATE " .. Prefix .. "_members SET `id_group`='" .. gid .."' WHERE `id_member`='" ..Args1['id_member'] .. "'")
						end
					end
					query2:start()
				else
					ServerLog("[gForum] Tried to set rank on unlinked user.")
					addlog("[gForum] Tried to set rank on unlinked user.")
				end
			end
			query1:start()
		else
			error( "Forum is not smf" )
		end
	end,
	[ "darkrp_money" ] = function( data, args, ply )
		if not IsValid( ply ) then
			error( "Player is not valid" )
		end
		local succ, err = pcall( function( ) 
			local PLAYER = FindMetaTable( "Player" )
			if type( PLAYER.AddMoney ) == "function" then
				ply:AddMoney( tonumber( args[ 1 ] ) )
			elseif type( PLAYER.addMoney ) == "function" then
				ply:addMoney( tonumber( args[ 1 ] ) )
			else 
				error( "No functions were found" )
			end	
		end )
		if not succ then
			error( err )
		end	
	end,
	[ "pointshop_points" ] = function( data, args, ply )
		if not IsValid( ply ) then
			error( "Player is not valid" )
		end
		local succ, err = pcall( function() ply:PS_GivePoints( tonumber( args[ 1 ] ) ) end )
		if not succ then
			error( err )
		end	
	end,
	["print"] = function( data, args, ply )
		if not IsValid( ply ) then
			error( "Player is not valid" )
		end
		if type( args ) == "table" then
			for i=1, #args do
				if type( args[ i ] ) == "table" then
					args[ i ] = Color( args[ i ][ 1 ], args[ i ][ 2 ], args[ i ][ 3 ] )
				elseif type( args[ i ] ) == "string" then
					args[ i ] = string.Replace( args[ i ], "%name%", tostring( data.playername ) )
					args[ i ] = string.Replace( args[ i ], "%orderid%", tostring( data.id ) )
					args[ i ] = string.Replace( args[ i ], "%transactionid%", tostring( data.transactionid ) )
					args[ i ] = string.Replace( args[ i ], "%packageid%", tostring( data.packageid ) )
					args[ i ] = string.Replace( args[ i ], "%gamename%", tostring( ply:Name( ) ) )
					args[ i ] = string.Replace( args[ i ], "%steamid%", tostring( ply:SteamID( ) ) )
					args[ i ] = string.Replace( args[ i ], "%steamid64%", tostring( ply:SteamID64( ) ) )
					args[ i ] = string.Replace( args[ i ], "%uniqueid%", tostring( ply:UniqueID( ) ) )
					args[ i ] = string.Replace( args[ i ], "%userid%", tostring( ply:UserID( ) ) )
				end
			end
			net.Start( "DonationSystemColorChat" )
				net.WriteTable( args )
			net.Send( ply )
		end
	end,
	[ "broadcast" ] = function( data, args, ply )
		if type( args ) == "table" then
			for i=1, #args do
				if type( args[ i ] ) == "table" then
					args[ i ] = Color( args[ i ][ 1 ], args[ i ][ 2 ], args[ i ][ 3 ] )
				elseif type( args[ i ] ) == "string" then
					args[ i ] = string.Replace( args[ i ], "%name%", tostring( data.playername ) )
					args[ i ] = string.Replace( args[ i ], "%orderid%", tostring( data.id ) )
					args[ i ] = string.Replace( args[ i ], "%transactionid%", tostring( data.transactionid ) )
					args[ i ] = string.Replace( args[ i ], "%packageid%", tostring( data.packageid ) )
					args[ i ] = string.Replace( args[ i ], "%steamid%", tostring( data.playerid ) )
					args[ i ] = string.Replace( args[ i ], "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
					if IsValid( ply ) then
						args[ i ] = string.Replace( args[ i ], "%steamid64%", tostring( ply:SteamID64( ) ) )
						args[ i ] = string.Replace( args[ i ], "%gamename%", tostring( ply:Nick( ) ) )
						args[ i ] = string.Replace( args[ i ], "%userid%", tostring( ply:UserID( ) ) )
					end
				end
			end
			net.Start( "DonationSystemColorChat" )
				net.WriteTable( args )
			net.Broadcast( )
		end
	end,
	[ "broadcast_omit" ] = function( data, args, ply )
		if type( args ) == "table" then
			for i=1, #args do
				if type( args[ i ] ) == "table" then
					args[ i ] = Color( args[ i ][ 1 ], args[ i ][ 2 ], args[ i ][ 3 ] )
				elseif type( args[ i ] ) == "string" then
					args[ i ] = string.Replace( args[ i ], "%name%", tostring( data.playername ) )
					args[ i ] = string.Replace( args[ i ], "%orderid%", tostring( data.id ) )
					args[ i ] = string.Replace( args[ i ], "%transactionid%", tostring( data.transactionid ) )
					args[ i ] = string.Replace( args[ i ], "%packageid%", tostring( data.packageid ) )
					args[ i ] = string.Replace( args[ i ], "%steamid%", tostring( data.playerid ) )
					args[ i ] = string.Replace( args[ i ], "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
					if IsValid( ply ) then
						args[ i ] = string.Replace( args[ i ], "%steamid64%", tostring( ply:SteamID64( ) ) )
						args[ i ] = string.Replace( args[ i ], "%gamename%", tostring( ply:Nick( ) ) )
						args[ i ] = string.Replace( args[ i ], "%userid%", tostring( ply:UserID( ) ) )
					end
				end
			end
			net.Start( "DonationSystemColorChat" )
				net.WriteTable( args )
				
			if IsValid(ply) then
				net.SendOmit( ply )
			else
				net.Broadcast( )
			end
		end
	end,
	[ "lua" ] = function( data, args, ply )
		local oldPLAYER, oldSTEAMID, oldORDERDATA, oldQUERY = PLAYER, STEAMID, ORDERDATA, QUERY
		PLAYER, STEAMID, ORDERDATA, QUERY = ply, data.playerid, data, query
	
		local func = CompileString( args[ 1 ], "[DonationSystem] Lua", true )
		if type(func) == "function" then
			func()
		else
			PLAYER, STEAMID, ORDERDATA, QUERY = oldPLAYER, oldSTEAMID, oldORDERDATA, oldQUERY
			error(func)
		end
		
		PLAYER, STEAMID, ORDERDATA, QUERY = oldPLAYER, oldSTEAMID, oldORDERDATA, oldQUERY
	end,
	[ "sv_cmd" ] = function( data, args, ply )
		for i=1, #args do
			args[ i ] = string.Replace( args[ i ], "%name%", tostring( data.playername ) )
			args[ i ] = string.Replace( args[ i ], "%orderid%", tostring( data.id ) )
			args[ i ] = string.Replace( args[ i ], "%transactionid%", tostring( data.transactionid ) )
			args[ i ] = string.Replace( args[ i ], "%packageid%", tostring( data.packageid ) )
			args[ i ] = string.Replace( args[ i ], "%steamid%", tostring( data.playerid ) )
			args[ i ] = string.Replace( args[ i ], "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
			if IsValid( ply ) then
				args[i] = string.Replace( args[i], "%steamid64%", tostring( ply:SteamID64( ) ) )
				args[i] = string.Replace( args[i], "%gamename%", tostring( ply:Nick( ) ) )
				args[i] = string.Replace( args[i], "%userid%", tostring( ply:UserID( ) ) )
			end
		end
		RunConsoleCommand( unpack( args ) )
	end,
	[ "disabled" ] = function( data, args, ply ) end,
	[ "cl_cmd" ] = function( data, args, ply )
		if not IsValid( ply ) then
			error( "Player is not valid" )
		end
		for i=1, #args do
			args[ i ] = string.Replace( args[ i ], "%name%", tostring( data.playername ) )
			args[ i ] = string.Replace( args[ i ], "%orderid%", tostring( data.id ) )
			args[ i ] = string.Replace( args[ i ], "%transactionid%", tostring( data.transactionid ) )
			args[ i ] = string.Replace( args[ i ], "%packageid%", tostring( data.packageid ) )
			args[ i ] = string.Replace( args[ i ], "%steamid%", tostring( data.playerid ) )
			args[ i ] = string.Replace( args[ i ], "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
			
			args[ i ] = string.Replace( args[ i ], "%steamid64%", tostring( ply:SteamID64( ) ) )
			args[ i ] = string.Replace( args[ i ], "%gamename%", tostring( ply:Nick( ) ) )
			args[ i ] = string.Replace( args[ i ], "%userid%", tostring( ply:UserID( ) ) )
		end
		net.Start( "DonationSystemCmd" )
			net.WriteTable( args )
		net.Send( ply )
	end,
	[ "sql" ] = function( data, args, ply )
		local querystring = args.query or args[ 1 ]
		querystring = string.Replace( querystring, "%name%", tostring( data.playername ) )
		querystring = string.Replace( querystring, "%orderid%", tostring( data.id ) )
		querystring = string.Replace( querystring, "%transactionid%", tostring( data.transactionid ) )
		querystring = string.Replace( querystring, "%packageid%", tostring( data.packageid ) )
		querystring = string.Replace( querystring, "%steamid%", tostring( data.playerid ) )
		querystring = string.Replace( querystring, "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
		querystring = string.Replace( querystring, "%name_esc%", db:escape( tostring( data.playername ) ) )
		querystring = string.Replace( querystring, "%ostime%", tostring( os.time( ) ) )
		
		if IsValid( ply ) then
			querystring = string.Replace( querystring, "%steamid64%", tostring( ply:SteamID64( ) ) )
			querystring = string.Replace( querystring, "%gamename%", tostring( ply:Nick( ) ) )
			querystring = string.Replace( querystring, "%gamename_esc%", db:escape( tostring( ply:Name( ) ) ) )
			querystring = string.Replace( querystring, "%userid%", tostring( ply:UserID( ) ) )
		end
		
		query( querystring )
	end,
	[ "sql_ext" ] = function( data, args,ply )
		local querystring = args.query
		querystring = string.Replace( querystring, "%name%", tostring( data.playername ) )
		querystring = string.Replace( querystring, "%orderid%", tostring( data.id ) )
		querystring = string.Replace( querystring, "%transactionid%", tostring( data.transactionid ) )
		querystring = string.Replace( querystring, "%packageid%", tostring( data.packageid ) )
		querystring = string.Replace( querystring, "%steamid%", tostring( data.playerid ) )
		querystring = string.Replace( querystring, "%uniqueid%", tostring( util.CRC( "gm_" .. data.playerid .. "_gm" ) ) )
		querystring = string.Replace( querystring, "%name_esc%", db:escape( tostring( data.playername ) ) )
		querystring = string.Replace( querystring, "%ostime%", tostring( os.time( ) ) )
		
		if IsValid(ply) then
			querystring = string.Replace( querystring, "%steamid64%", tostring(ply:SteamID64()) )
			querystring = string.Replace( querystring, "%gamename%", tostring(ply:Nick()) )
			querystring = string.Replace( querystring, "%gamename_esc%", db:escape(tostring(ply:Name())) )
			querystring = string.Replace( querystring, "%userid%", tostring(ply:UserID()) )
		end
		
		local db = mysqloo.connect( args.host, args.database, args.username, args.password, 3306 )
		
		function db:onConnected( )
			local q = self:query( querystring )
			function q:onSuccess( data ) end
			function q:onError( err, sql )
				addlog( "Error executing 'sql_ext' command, error: " .. err .. " sql: " .. sql )
			end
			q:start( )
		end
		function db:onConnectionFailed( err )
			addlog( "Error executing 'sql_ext' command, error: ", err )
		end

		db:connect( )
	end,
	[ "cancel" ] = function( data, args, ply )
		local excludeself, serverid, packageid, online, delay, commandid
	
		if type(args[1]) == "table" then
			excludeself = args[1].excludeself
			serverid = args[1].serverid
			packageid = args[1].packageid
			online = args[1].online
			delay = args[1].delay
			commandid = args[1].commandid
		else
			excludeself = args[ 1 ]
			serverid = args[ 2 ]
			packageid = args[ 3 ]
			online = args[ 4 ]
			delay = args[ 5 ]
			commandid = args[ 6 ]		
		end
		
		local querystr = "UPDATE commands SET activated = 1 WHERE playerid = \"" .. db:escape( data.playerid ) .. "\""
		if excludeself then querystr = querystr .. " AND transactionid <> \"" .. db:escape( data.transactionid ) .. "\"" end
		if serverid then querystr = querystr .. " AND serverid = \"" .. db:escape( serverid ) .. "\""end
		if packageid then querystr = querystr .. " AND packageid = \"" .. db:escape( packageid ) .. "\"" end
		if online then querystr = querystr .. " AND online = \"" .. ( online and 1 or 0 ) .. "\"" end
		if delay then querystr = querystr .. " AND delay = \"" .. db:escape( delay ) .. "\"" end
		if commandid then querystr = querystr .. " AND commandid = \"" .. db:escape( commandid ) .. "\"" end
		query( querystr )
	end
}

---- Main Timer -----------------------------------------------

local activated = {}
timer.Create( "DonationSystemCheck", 60, 0, function( ) -- Check database every 60 seconds
	-- Store players to table, and add players conditions to sql query.
	local plyEnts = { }
	local sqlplayers = ""
	for _, ply in pairs( player.GetAll( ) ) do
		if (Forum != "smf" and ply:TimeConnected( ) > 60) or (Forum == "smf" and ply.Registered) then -- Making sure player is fully initialised
			plyEnts[ ply:SteamID( ) ] = ply
			sqlplayers = sqlplayers .. " OR playerid = \"" .. db:escape( ply:SteamID( ) ) .. "\""
		end
	end
	
	-- Get all commands that should be activated in next 60 seconds
	query("SELECT *, ( UNIX_TIMESTAMP() ) AS unixtime FROM `commands` WHERE serverid = \"" .. db:escape( SERVER_ID ) .. "\" AND activated = 0 AND activatetime <= (UNIX_TIMESTAMP()+59) AND ( online = 0 " .. sqlplayers .. ")", function( commands ) -- " AND delay <= 76561198000622892 - 279"
		for _, cmddata in pairs( commands ) do
			-- Delay the command
			local timeoffset = math.max( 1, cmddata.activatetime - cmddata.unixtime  )
			
			timer.Simple( timeoffset, function( )
				if cmddata.online == 0 or IsValid( plyEnts[ cmddata.playerid ] ) then  -- Check if player still on the server
					query("UPDATE `commands` SET activated='1' WHERE id=" .. cmddata.id, function( data, q ) -- Activate it
						if q:affectedRows( ) > 0 and not activated[ cmddata.id ] then -- Making sure the command is not activated before to prevent duplicate execution
							if cmddata.online == 0 or IsValid( plyEnts[ cmddata.playerid ] ) then
								activated[ cmddata.id ] = true
								local args = util.JSONToTable( cmddata.command )
								local command = table.remove( args, 1 )
								
								addlog( "Executing " .. ( cmddata.online == 1 and "online" or "offline" ) .. " command '" .. command .. "'(" .. cmddata.id .. ") for " .. cmddata.playername .. "(" .. cmddata.playerid .. ")\nCommand Data:" .. cmddata.command)
								
								local succ, err = pcall( function( ) DS.Commands[ command ]( cmddata, args, plyEnts[ cmddata.playerid ] ) end )
								if not succ then
									addlog( "Error while executing command '" .. command .. "'(" .. cmddata.id .. "). Error: " .. err )
								end
								
							else
								query("UPDATE `commands` SET activated='0' WHERE id=" .. cmddata.id) -- If player disconnect, we might want to deactivate the command
							end
						else
							addlog( "Error executing command (" .. cmddata.id .. "). Failed to mark command as activated. " .. tostring( IsValid( plyEnts[ cmddata.playerid ] ) ) )
						end
					end )
				end
			end )
		end
	end )
end )