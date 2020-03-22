PermWeapons = {}
PermWeapons.Config = {}

-- Only enable one to prevent giving ammo/weapon twice
PermWeapons.Config.GiveOnSpawn = true
PermWeapons.Config.GiveOnLoadout = false --Some gamemodes doesn't call loadout hook.

-- Groups

PermWeapons.Config.Groups = {}
-- Example
--[[
PermWeapons.Config.Groups = {
	["vip"] = {
		["awesome_gun1"] = { isAmmo = false, amount = 0 },
		["awesome_gun2"] = { isAmmo = false, amount = 0 },
		["awesome_gun3"] = { isAmmo = false, amount = 0 }
	},
	["moderator"] = {
		["banhammer"] = { isAmmo = false, amount = 0 }
	},
	["admin"] = {
		["banhammer"] = { isAmmo = false, amount = 0 },
		["awesome_gun1"] = { isAmmo = false, amount = 0 }
	}
}
]]
---------------------------------------------------------------

PermWeapons.Data = {}

---------------------------------------------------------------

function ToSteamID( ply )
	if type(ply) == "Player" then
		return ply:SteamID() 
	elseif type(ply) == "string" then
		return string.match( string.upper(ply) , "^STEAM_%d+:%d+:%d+$" )
	end
	return nil
end

---------------------------------------------------------------
-- Database Management ----------------------------------------
---------------------------------------------------------------

function PermWeapons.CheckTables()
	if not sql.TableExists("permweaps_data") then
		local query = "CREATE TABLE permweaps_data ( steamid varchar(22) NOT NULL, data TEXT NOT NULL, UNIQUE (steamid) )"
		local result = sql.Query(query)
		if (sql.TableExists("permweaps_data")) then
			Msg( "[PermWeapons] Succesfully created table permweaps_data! \n" )
			return true
		else
			Msg( "[PermWeapons] Failed to create permweaps_data table ! \n" )
			Msg( "[PermWeapons] Error: " .. sql.LastError( result ) .. "\n" )
			return false
		end	
	end
	return true
end

function PermWeapons.LoadData( ply )
	if not PermWeapons.CheckTables() then return nil end
	if not ply then
		local result = sql.Query("SELECT * FROM permweaps_data")
		for k, v in pairs( result ) do
			local steamid = v.steamid
			local data = util.JSONToTable( v.data )
			
			PermWeapons.Data[steamid] = data
		end
	else
		local steamid = ToSteamID(ply)
		if not steamid then
			Msg( "[PermWeapons] Failed to load data, player not found. " .. tostring(ply))
			return false
		end
		
		local result = sql.Query("SELECT * FROM permweaps_data WHERE steamid = '" .. steamid .. "'")
		if result then
			PermWeapons.Data[steamid] = util.JSONToTable( result[1]["data"] )
		else
			PermWeapons.Data[steamid] = {}
		end
	end
	
	return true
end

function PermWeapons.SaveData( ply )
	if not PermWeapons.CheckTables() then return false end
	if not ply then
		for steamid, data in pairs( PermWeapons.Data ) do
			local jsondata = util.TableToJSON(data)
			sql.Query("REPLACE INTO permweaps_data ( `steamid` , `data` ) VALUES ( " .. sql.SQLStr( steamid ) .. ", " .. sql.SQLStr( jsondata ) .. " )")
		end
	else
		local steamid = ToSteamID(ply)
		if not steamid then
			Msg( "[PermWeapons] Failed to save data, player not found. " .. tostring(ply))
			return false
		end
		
		local jsondata = util.TableToJSON( PermWeapons.Data[steamid] )
		sql.Query("REPLACE INTO permweaps_data ( `steamid` , `data` ) VALUES ( " .. sql.SQLStr( steamid ) .. ", " .. sql.SQLStr( jsondata ) .. " )")
	end
	
	return true
end

---------------------------------------------------------------

function PermWeapons.AddWeapon( ply, class, isAmmo, amount )
	local steamid = ToSteamID(ply)
	if not steamid then
		Msg( "[PermWeapons] Failed to add weapons, player not found.")
		return false
	end
	if not PermWeapons.CheckTables() then return false end
	if not PermWeapons.Data[steamid] and not PermWeapons.LoadData( ply ) then return false end
	
	PermWeapons.Data[steamid][class] = {
		isAmmo = isAmmo or false,
		amount = amount or 0
	}
	
	return PermWeapons.SaveData( ply )
end
function PermWeapons.RemoveWeapon( ply, class )
	local steamid = ToSteamID(ply)
	if not steamid then
		Msg( "[PermWeapons] Failed to add weapons, player not found.")
		return false
	end
	if not PermWeapons.Data[steamid] and not PermWeapons.LoadData( ply ) then return false end
	
	PermWeapons.Data[steamid][class] = nil
	
	return PermWeapons.SaveData()
end

---------------------------------------------------------------
-- Console Commands -------------------------------------------
---------------------------------------------------------------

concommand.Add( "pw_add_wep", function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return nil end
	
	if #args < 2 then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Not enough arguments. 2 expected, got " .. #args )
		else
			Msg( "Not enough arguments. 2 expected, got " .. #args ..".\n")
		end
		return nil
	end
	
	local steamid = ToSteamID(args[1])
	local class = string.match( string.lower(args[2]), "^[%l_%d]+$" )
	
	if not steamid then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Player not found.")
		else
			Msg( "Player not found.\n")
		end
		return nil
	end
	if not class then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Invalid class argument.")
		else
			Msg( "Invalid class argument.\n")
		end
		return nil
	end
	
	if PermWeapons.AddWeapon( steamid, class ) then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Successfully added weapon " .. class .. " to " .. steamid .. ".")
		else
			Msg( "Successfully added weapon " .. class .. " to " .. steamid .. ".\n")
		end
	end
end )

concommand.Add( "pw_add_ammo", function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return nil end
	
	if #args < 3 then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Not enough arguments. 3 expected, got " .. #args )
		else
			Msg( "Not enough arguments. 3 expected, got " .. #args ..".\n")
		end
		return nil
	end
	
	local steamid = ToSteamID(args[1])
	local class = string.match( string.lower(args[2]), "^[%l_%d]+$" )
	local amount = tonumber(args[3])
	
	if not steamid then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Player not found.")
		else
			Msg( "Player not found.\n")
		end
		return nil
	end
	if not class then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Invalid class argument.")
		else
			Msg( "Invalid class argument.\n")
		end
		return nil
	end
	if not amount or amount<=0 then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Invalid amount argument.")
		else
			Msg( "Invalid amount argument.\n")
		end
		return nil
	end
	if PermWeapons.AddWeapon( steamid, class, true, amount ) then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Successfully added ammo " .. class .. "(" .. amount .. ") to " .. steamid .. "!")
		else
			Msg( "Successfully added ammo " .. class .. "(" .. amount .. ") to " .. steamid .. "!\n")
		end
	end
end )

concommand.Add( "pw_remove", function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return nil end
	
	if #args < 2 then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Not enough arguments. 2 expected, got " .. #args)
		else
			Msg( "Not enough arguments. 2 expected, got " .. #args ..".\n")
		end
		return nil
	end
	
	local steamid = ToSteamID(args[1])
	local class = string.match( string.lower(args[2]), "^[%l_%d]+$" )
	
	if not steamid then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Player not found.")
		else
			Msg( "Player not found.\n")
		end
		return nil
	end
	if not class then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Invalid class argument.")
		else
			Msg( "Invalid class argument.\n")
		end
		return nil
	end
	
	if PermWeapons.RemoveWeapon( steamid, class ) then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Successfully removed " .. class .. " from " .. steamid .. ".")
		else
			Msg( "Successfully removed " .. class .. " from " .. steamid .. ".\n")
		end
	end
end )

concommand.Add( "pw_list", function( ply, cmd, args, str )
	if IsValid( ply ) and not ply:IsSuperAdmin() then return nil end
	
	if #args < 1 then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Not enough arguments. 1 expected, got " .. #args)
		else
			Msg( "Not enough arguments. 1 expected, got " .. #args ..".\n")
		end
		return nil
	end
	
	local steamid = ToSteamID(args[1])
	
	if not steamid then
		if IsValid( ply ) then
			ply:PrintMessage( HUD_PRINTCONSOLE, "Player not found.")
		else
			Msg( "Player not found.\n")
		end
		return nil
	end
	if not PermWeapons.Data[steamid] and not PermWeapons.LoadData( steamid ) then return nil end
	
	if IsValid( ply ) then
		ply:PrintMessage( table.ToString( PermWeapons.Data[steamid] , steamid, true ) )
	else
		MsgN( table.ToString( PermWeapons.Data[steamid] , steamid, true ) )
	end
end )

---------------------------------------------------------------
-- Loadout ----------------------------------------------------
---------------------------------------------------------------

hook.Add( "PlayerInitialSpawn", "PermWeapons_InitialSpawn", function( ply )
	local steamid = ToSteamID(ply)
	if not PermWeapons.Data[steamid] then
		PermWeapons.LoadData( steamid )
	end
end )

function PermWeapons.Loadout( ply )
	local steamid = ToSteamID(ply)
	if not PermWeapons.Data[steamid] and not PermWeapons.LoadData( ply ) then return nil end
	for class, data in pairs(PermWeapons.Data[steamid]) do
		if data.isAmmo then
			ply:GiveAmmo( data.amount, class, true )
		else
			ply:Give( class )
		end
	end
	if PermWeapons.Config.Groups[ ply:GetUserGroup( ) ] then
		for class, data in pairs( PermWeapons.Config.Groups[ ply:GetUserGroup( ) ] ) do
			if data.isAmmo then
				ply:GiveAmmo( data.amount, class, true )
			else
				ply:Give( class )
			end
		end
	end
end

if PermWeapons.Config.GiveOnSpawn then
	hook.Add( "PlayerSpawn", "PermWeapons_Loadout", function( ply )
		timer.Simple( 0.5, function() PermWeapons.Loadout( ply ) end )
	end)
end
if PermWeapons.Config.GiveOnLoadout then
	hook.Add( "PlayerLoadout", "PermWeapons_Loadout", function( ply )
		PermWeapons.Loadout( ply )	
	end)
end