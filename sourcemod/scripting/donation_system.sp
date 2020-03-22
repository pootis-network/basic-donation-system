#pragma semicolon 1
#include <sourcemod>
#include <smjansson>


public Plugin:myinfo = {
	name = "Donation System",
	author = "DarkE",
	description = "Donation System for sourcemod",
	version = "1.4.1",
	url = "http://cdrh.ir/rnnx"
};
new Handle:hServerID;
public OnPluginStart() {
	hServerID = CreateConVar("ds_serverid", "", "Donation System ServerID");
	if( SQL_CheckConfig("donations")){
		SQL_TConnect(DonationSystemConnected, "donations");
	}else{
		LogError("No 'donations' configuration was found in databases.cfg." );
	}
	CreateTimer(60.0, DonationSystemTimer, _, TIMER_REPEAT);
}


new Handle:hDatabase = INVALID_HANDLE;
public Action:DonationSystemTimer(Handle:timer){
	decl String:serverid[128];
 
	GetConVarString(hServerID, serverid, sizeof(serverid));
	if( StrEqual(serverid,"") ){
		LogError("ServerID is invalid, use ds_serverid to change serverid" );
		return Plugin_Continue;
	}
	if( !SQL_CheckConfig("donations")){
		LogError("No 'donations' configuration was found in databases.cfg." );
		return Plugin_Continue;
	}
	if (hDatabase == INVALID_HANDLE){
		SQL_TConnect(DonationSystemConnected, "donations");
		return Plugin_Continue;
	}
	new String:sqlplayers[38*MAXPLAYERS] = "";
	
	for (new client = 1; client <= MaxClients; client++){
		if ( IsClientInGame(client) ){
			decl String:gAuth[21];
			GetClientAuthString(client, gAuth, 21);
			if ( !StrEqual(gAuth, "BOT") ){
				Format(sqlplayers, sizeof(sqlplayers), "%s OR playerid = \"%s\"",sqlplayers, gAuth);
			}
		}
	}
	decl String:query[157+128+38*MAXPLAYERS];
	
	Format(query, sizeof(query), "SELECT id, activatetime, playerid, online, command, playername, transactionid, ( UNIX_TIMESTAMP() ) AS unixtime FROM `commands` WHERE serverid = \"%s\" AND activated = 0 AND activatetime <= (UNIX_TIMESTAMP()+59) AND ( online = 0%s )",serverid, sqlplayers);
	SQL_TQuery(hDatabase, CheckCommands, query);
	
	return Plugin_Continue;
}


public DonationSystemConnected(Handle:owner, Handle:hndl, const String:error[], any:data){
	if (hndl == INVALID_HANDLE){
		LogError("Database failure: %s", error);
	} else {
		hDatabase = hndl;
		SQL_FastQuery(hndl , "CREATE TABLE IF NOT EXISTS `commands` ( `id` MEDIUMINT NOT NULL AUTO_INCREMENT, `serverid` varchar(40) NOT NULL, `packageid` int(6) unsigned NOT NULL, `online` TINYINT(1) UNSIGNED NOT NULL, `commandid` int(6) unsigned NOT NULL, `delay` INT UNSIGNED NOT NULL, `activatetime` INT UNSIGNED NOT NULL, `command` TEXT NOT NULL, `activated` TINYINT(1) UNSIGNED NOT NULL, `transactionid` varchar(125) NOT NULL, `playerid` varchar(40) NOT NULL, `playername` varchar(40) NOT NULL, PRIMARY KEY (`id`) );");
	}
}

public CheckCommands(Handle:owner, Handle:query, const String:error[], any:data){
	while(	SQL_FetchRow(query) ){
		new id, activatetime, online, unixtime;
		decl String:playerid[21];
		decl String:command[1024];
		decl String:playername[40];
		decl String:transactionid[30];
		
		id = SQL_FetchInt(query,0);
		activatetime = SQL_FetchInt(query,1);
		SQL_FetchString(query,2,playerid,21);
		online = SQL_FetchInt(query,3);
		SQL_FetchString(query,4,command,1024);
		SQL_FetchString(query,5,playername,40);
		SQL_FetchString(query,6,transactionid,30);
		unixtime = SQL_FetchInt(query,7);
		
		new delay = activatetime - unixtime;
		if( delay < 1 ){
			delay = 1;
		}
		
		new Handle:pack = CreateDataPack();
		WritePackCell(pack, id);
		WritePackString(pack, playerid);
		WritePackCell(pack, online);
		WritePackString(pack, command);
		WritePackString(pack, playername);
		WritePackString(pack, transactionid);
		
		CreateTimer(float(delay), CommandActivate, pack);
	} 
}

public Action:CommandActivate(Handle:timer, any:pack){
	new id, online;
	decl String:playerid[21];
	decl String:command[1024];
	decl String:playername[40];
	decl String:transactionid[30];
	
	ResetPack(pack);
	
	id = ReadPackCell(pack);
	ReadPackString(pack, playerid, sizeof(playerid));
	online = ReadPackCell(pack);
	ReadPackString(pack, command, sizeof(command));
	ReadPackString(pack, playername, sizeof(playername));
	ReadPackString(pack, transactionid, sizeof(transactionid));
	CloseHandle(pack);
	new serial;
	
	for (new client = 1; client <= MaxClients; client++){
		if ( IsClientInGame(client) ){
			decl String:gAuth[21];
			GetClientAuthString(client, gAuth, 21);
			if ( StrEqual(gAuth, playerid) ){
				serial = GetClientSerial(client);
				break;
			}
		}
	}
	
	new Handle:npack = CreateDataPack();
	WritePackCell(npack, id);
	WritePackString(npack, playerid);
	WritePackCell(npack, online);
	WritePackString(npack, command);
	WritePackString(npack, playername);
	WritePackString(npack, transactionid);
	WritePackCell(npack, serial);
	
	
	decl String:query[60];
	Format(query, sizeof(query), "UPDATE `commands` SET activated='1' WHERE id=%i", id); 
	if( online == 0 || serial != 0 ){
		SQL_TQuery(hDatabase, ExecuteCommands, query, npack);
	}
}
public ExecuteCommands(Handle:owner, Handle:query, const String:error[], any:pack){
	if(	SQL_GetAffectedRows(owner) ){
		new id, online, serial;
		decl String:playerid[21];
		decl String:command[1024];
		decl String:playername[40];
		decl String:transactionid[30];
	

		ResetPack(pack);
		
		id = ReadPackCell(pack);
		ReadPackString(pack, playerid, sizeof(playerid));
		online = ReadPackCell(pack);
		ReadPackString(pack, command, sizeof(command));
		ReadPackString(pack, playername, sizeof(playername));
		ReadPackString(pack, transactionid, sizeof(transactionid));
		serial = ReadPackCell(pack);	
		CloseHandle(pack);
		
		new client = GetClientFromSerial(serial);
		decl String:gAuth[21];
		gAuth = playerid;
		decl String:gamename[40] = "";
		gamename = playername;
		
		if( client ){
			GetClientAuthString(client, gAuth, 21);
			GetClientName(client, gamename, 40);
		}
		if ( StrEqual(gAuth,playerid) && ((online == 1 && client != 0) || online == 0)){
			
			new Handle:json = json_load(command);	
			decl String:cmd[20];
			json_array_get_string(json, 0,cmd, 20);
			
			if ( StrEqual(cmd,"sv_cmd") ){
				decl String:exec_str[100] = "";
				json_array_get_string(json, 1,exec_str, 100);
				
				ReplaceString(gamename,40,";","");
				ReplaceString(playername,40,";","");
				
				ReplaceString(exec_str,100,"%steamid%",playerid);
				ReplaceString(exec_str,100,"%gamename%",gamename);
				ReplaceString(exec_str,100,"%name%",playername);
				ReplaceString(exec_str,100,"%transactionid%",transactionid);
				ServerCommand(exec_str); 
			}else if (StrEqual(cmd,"cancel")){
				new c_excludeself, c_packageid, c_delay, c_commandid = 0;
				new bool:c_online;
				decl String:c_serverid[128];
				if (json_array_size(json) > 1)
					c_excludeself = json_array_get_int(json, 1);
				if (json_array_size(json) > 2)
					json_array_get_string(json, 2, c_serverid,128);
				if (json_array_size(json) > 3)
					c_packageid = json_array_get_int(json, 3);
				if (json_array_size(json) > 4)
					c_online = json_array_get_bool(json, 4);
				if (json_array_size(json) > 5)
					c_delay = json_array_get_int(json, 5);
				if (json_array_size(json) > 6)
					c_commandid = json_array_get_int(json, 6);
				
				
				if (json_array_size(json) > 7){
					LogError( "Error executing 'cancel' command, Error: Arguments can't exceed 6" );
				}else{
					decl String:nquery[200];
					Format(nquery, sizeof(nquery), "UPDATE commands SET activated = 1 WHERE playerid = \"%s\"", playerid); 
					
					if (json_array_size(json) > 1 && c_excludeself == 1)
						Format(nquery, sizeof(nquery), "%s AND transactionid <> \"%s\"", nquery, transactionid); 
					if (json_array_size(json) > 2)
						Format(nquery, sizeof(nquery), "%s AND serverid = \"%s\"", nquery, c_serverid); 
					if (json_array_size(json) > 3)
						Format(nquery, sizeof(nquery), "%s AND packageid = \"%s\"", nquery, c_packageid); 
					if (json_array_size(json) > 4)
						Format(nquery, sizeof(nquery), "%s AND online = \"%s\"", nquery, (c_online ? 1 : 0)); 
					if (json_array_size(json) > 5)
						Format(nquery, sizeof(nquery), "%s AND delay = \"%s\"", nquery, c_delay); 
					if (json_array_size(json) > 6)
						Format(nquery, sizeof(nquery), "%s AND commandid = \"%s\"", nquery, c_commandid); 
					
					SQL_FastQuery(hDatabase , nquery);
				}
			}else if (StrEqual(cmd,"add_admin")){
				decl String:flags[50] = "";
				decl String:group[128] = "";
				new immunity = -1;
				json_array_get_string(json, 1,flags, 50);
				
				if (json_array_size(json) > 2){
					if(!json_array_get_string(json, 2,group, 50)){
						immunity = json_array_get_int(json, 2);
						if (json_array_size(json) > 3)
							json_array_get_string(json, 3,group, 50);
					}
						
				}
				
				new Handle:h_AdminList, String:s_AdminList[PLATFORM_MAX_PATH];
				BuildPath(Path_SM, s_AdminList, sizeof(s_AdminList), "configs/admins.cfg");
				
				new String:buffer[PLATFORM_MAX_PATH];
				new bool:Ignore;

				h_AdminList = CreateKeyValues("Admins");

				BuildPath(Path_SM, buffer, sizeof(buffer), "configs/admins2.cfg");

				new Handle:filehandle = OpenFile(s_AdminList, "r");
				new Handle:file2 = OpenFile(buffer, "w");

				while(!IsEndOfFile(filehandle)){
					new String:Line[PLATFORM_MAX_PATH];
					ReadFileLine(filehandle, Line, sizeof(Line));
					if (StrContains((Line), "/*") != -1){
						Ignore = true;
						continue;
					}
					if (StrContains((Line), "*/") != -1){
						Ignore = false;
						continue;
					}
					if (Ignore)
						continue;
					WriteFileString(file2, Line, false);
				}
				CloseHandle(filehandle);
				CloseHandle(file2);
				FileToKeyValues(h_AdminList, buffer);

				DeleteFile(buffer);
				KvRewind(h_AdminList);
				
				KvJumpToKey(h_AdminList, playerid, true);
				
				KvSetString(h_AdminList, "auth", "steam");
				KvSetString(h_AdminList, "identity", playerid);
				KvSetString(h_AdminList, "flags", flags);
				if (immunity >= 0){
					decl String:s_immunity[4] = "";
					IntToString(immunity,s_immunity,4);
					KvSetString(h_AdminList, "immunity", s_immunity);
				}
				if (!StrEqual(group, ""))
					KvSetString(h_AdminList, "group", group);
				
				
				KvRewind(h_AdminList);
				KeyValuesToFile(h_AdminList, s_AdminList);
				CloseHandle(h_AdminList);
			}else if (StrEqual(cmd,"remove_admin")){
				
				new Handle:h_AdminList, String:s_AdminList[PLATFORM_MAX_PATH];
				BuildPath(Path_SM, s_AdminList, sizeof(s_AdminList), "configs/admins.cfg");
				
				new String:buffer[PLATFORM_MAX_PATH];
				new bool:Ignore;

				h_AdminList = CreateKeyValues("Admins");

				BuildPath(Path_SM, buffer, sizeof(buffer), "configs/admins2.cfg");

				new Handle:filehandle = OpenFile(s_AdminList, "r");
				new Handle:file2 = OpenFile(buffer, "w");

				while(!IsEndOfFile(filehandle)){
					new String:Line[PLATFORM_MAX_PATH];
					ReadFileLine(filehandle, Line, sizeof(Line));
					if (StrContains((Line), "/*") != -1){
						Ignore = true;
						continue;
					}
					if (StrContains((Line), "*/") != -1){
						Ignore = false;
						continue;
					}
					if (Ignore)
						continue;
					WriteFileString(file2, Line, false);
				}
				CloseHandle(filehandle);
				CloseHandle(file2);
				FileToKeyValues(h_AdminList, buffer);

				DeleteFile(buffer);
				KvRewind(h_AdminList);
			
				if (KvJumpToKey(h_AdminList, playerid)){
					KvDeleteThis(h_AdminList);
				}
									
				KvRewind(h_AdminList);
				KeyValuesToFile(h_AdminList, s_AdminList);

				CloseHandle(h_AdminList);
			}else{
				LogError("There is no such command: %s", cmd);
			}
			
			CloseHandle(json);
			if(online){
				PrintToServer("Activated online command:%s(id:%i) for %s(%s)",cmd,id,gamename,playerid); 
			}else{
				PrintToServer("Activated offline command:%s(id:%i) for %s(%s)",cmd,id,playername,playerid); 
			}
		}else{
			decl String:nquery[60];
			Format(nquery, sizeof(nquery), "UPDATE `commands` SET activated='0' WHERE id=%i", id); 
			SQL_FastQuery(hDatabase , nquery);
		}
	}
}
