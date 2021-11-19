=================================
-------- Installation -----------
=================================
1. Extract permweapons.lua to garrysmod/lua/autorun/server/permweaps.lua.
2. Edit PermWeapons.Config.Groups table if you want to give weapons to specific groups. Please refer to the example provided inside the script.
3. If players not receiving weapons, try switching between PermWeapons.Config.GiveOnSpawn and PermWeapons.Config.GiveOnLoadout

=================================
-------- Commands ---------------
=================================

"pw_add_wep", <string:steamid>, <string:class>
	Description:	Adds 'class' weapon to the player
	Examples:
		Server Console:		pw_add_wep "STEAM_0:0:0" "weapon_ak47"
		DonationSystem: 	array( "sv_cmd", "pw_add_wep", "%steamid%", "weapon_ak47" )

"pw_add_ammo", <string:steamid>, <string:class>, <number:amount>
	Description:	Adds 'class' ammo to the player with specified amount
	Examples:
		Server Console:		pw_add_ammo "STEAM_0:0:0" "ammo_ak47" 100
		DonationSystem:		array( "sv_cmd", "pw_add_ammo", "%steamid%", "ammo_ak47", 100 )
	
"pw_remove", <string:steamid>, <string:class>
	Description:	Removes 'class' weapon/ammo from the player.
	Examples:
		Server Console:		pw_remove "STEAM_0:0:0" "weapon_ak47"
		DonationSystem:		array( "sv_cmd", "pw_remove", "%steamid%", "weapon_ak47" )

"pw_list", <string:steamid>
	Description:	Lists all weapons and ammo that the player currently have.
	Examples:
		Server Console:		pw_list "STEAM_0:0:0"