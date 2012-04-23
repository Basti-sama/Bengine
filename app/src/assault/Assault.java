/**
 * Core of the Bengine combat system.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: Assault.java 8 2010-10-17 20:55:04Z secretchampion $
 */

package assault;

import java.sql.*;
import java.text.DecimalFormat;
import java.util.*;
import java.io.FileOutputStream;

public class Assault
{
	public static long gentime;
	public static boolean debugmode = false; // On: Won't proceed any database updates + console outputs
	private static String dbhost = "localhost";
	private static String dbdatabase = "bengine";
	private static String username = "root";
	private static String dbpasswd = "";
	private static String prefix = "bengine_";
	public static int assaultid = 0;
	public static int planetid = 0;
	private static double[][] rapidfire = new double[100][100];
	public static Party party;
	public static int assaultResult;
	public static double metal = 0;
	public static double silicon = 0;
	public static double hydrogen = 0;
	private static int shotsAtter;
	private static int shotsDefender;
	private static int atterPower;
	private static int defenderPower;
	private static int shieldAtter;
	private static int shieldDefender;
	private static String assaultReport;
	private static String quantity = "";
	private static String guns = "";
	private static String shields = "";
	private static String shells = "";
	private static double atterUnitsLost = 0.0;
	private static double defenderUnitsLost = 0.0;
	private static double debrisMetal = 0.0;
	private static double debrisSilicon = 0.0;
	private static double moonChance = 0.0;
	public static double maxMoonChance = 20.0;
	private static boolean moon = false;
	private static boolean ismoon = false;
	public static String key;
	public static int time;
	private static final QuickRandom random = new QuickRandom();
	private static DecimalFormat decFormatter = new DecimalFormat(",###");
	public static Map<String, Integer> defenseRepaired = new HashMap<String, Integer>();
	public static boolean defenseIntoDebris = false;
	public static double[] bulkIntoDebris = new double[10];
	public static double defenseRepairMin = 0.6;
	public static double defenseRepairMax = 0.8;
	private static int configGroupId = 9;
	public static int haulMetal = 0;
	public static int haulSilicon = 0;
	public static int haulHydrogen = 0;
	private static boolean defenderZero = false;
	public static Calendar calObj = Calendar.getInstance();

	/**
	 * @param args
	 */
	public static void main(String[] args)
	{
		gentime = System.currentTimeMillis();
		if(args.length > 0)
		{
			dbhost = args[0];
			dbdatabase = args[3];
			username = args[1];
			dbpasswd = args[2];
			prefix = args[4];
			assaultid = Integer.valueOf(args[5]);
		}

		// Assault configuration
		bulkIntoDebris[3] = 0.3; // Fleet
		bulkIntoDebris[4] = 0.0; // Defense
		
		// Load configuration from database
		loadConfig();

		// Random key to protect the access
		key = generateKey(4);

		party = new Party(); // Initialize party
		
		assaultReport = "<div class=\"center\">";
		assaultReport += String
				.format(
						"{embedded[ASSAULT_TIME]}%ta %td. %tb %tY, %tT{/embedded}<br />\n<br /><br />\n",
						calObj, calObj, calObj, calObj, calObj);

		/**
		 * Load planet data
		 */
		int userid = 0;
		ResultSet rs = null;
		try
		{
			Statement stmt = Database.createStatement();
			rs = stmt
					.executeQuery("SELECT a.time, a.accomplished, p.planetid, p.metal, p.silicon, p.hydrogen, p.ismoon, g.moonid FROM "
							+ prefix
							+ "assault a LEFT JOIN "
							+ prefix
							+ "planet p ON (p.planetid = a.planetid) LEFT JOIN "
							+ prefix
							+ "galaxy g ON (a.planetid = g.planetid) WHERE a.assaultid = '"
							+ assaultid + "' LIMIT 1");
			if(rs.next())
			{
				if(rs.getInt("accomplished") == 1)
				{
					System.err.println("Combat is already accomplished.");
					System.exit(1);
				}
				planetid = rs.getInt("planetid");
				if(planetid > 0)
				{
					metal = (int) Math.floor(rs.getFloat("metal") / 2);
					silicon = (int) Math.floor(rs.getFloat("silicon") / 2);
					hydrogen = (int) Math.floor(rs.getFloat("hydrogen") / 2);
					if(rs.getInt("ismoon") == 1 || rs.getInt("moonid") > 0)
					{
						ismoon = true;
					}
				}
				time = rs.getInt("time"); // Assault time
			}
		}
		catch(SQLException e)
		{
			System.err.println(e.getMessage());
		}
		
		/**
		 * Read in users for this assault.
		 */
		try
		{
			Statement stmt = Database.createStatement();
			rs = stmt
					.executeQuery("SELECT u.userid, u.username, pp.mode, pp.participantid, pp.planetid, pp.preloaded, pp.consumption, pp.data, IFNULL(g.galaxy, m.galaxy) AS galaxy, IFNULL(g.system, m.system) AS system, IFNULL(g.position, m.position) AS position FROM "
							+ prefix
							+ "assaultparticipant pp LEFT JOIN "
							+ prefix
							+ "user u ON (u.userid = pp.userid) LEFT JOIN "
							+ prefix
							+ "galaxy g ON (g.planetid = pp.planetid) lEFT JOIN "
							+ prefix
							+ "galaxy m ON (m.moonid = pp.planetid) WHERE pp.assaultid = '"
							+ assaultid + "' ORDER BY pp.participantid ASC");
			while(rs.next())
			{
				userid = rs.getInt("userid");
				Participant participant = new Participant(userid, rs
						.getInt("mode"), rs.getString("username"));
				participant.setGalaxy(rs.getInt("galaxy"));
				participant.setSystem(rs.getInt("system"));
				participant.setPosition(rs.getInt("position"));
				participant.setParticipantId(rs.getInt("participantid"));
				participant.setConsumption(rs.getInt("consumption"));
				participant.setPreloaded(rs.getInt("preloaded"));
				if(rs.getString("data") != "" && rs.getString("data") != null)
				{
					participant.setData(rs.getString("data"));
				}
				participant.loadShips();
				if(rs.getInt("mode") == 1)
				{
					party.addAtter(participant);
				}
				else
				{
					party.addDefender(participant);
				}
			}
		}
		catch(SQLException e)
		{
			System.err.println(e.getMessage());
		}
		
		if(party.defenderHasNoFleet())
		{
			defenderZero = true;
			assaultResult = 1;
		}
		else
		{
			/**
			 * Load rapid fire.
			 */
			rs = null;
			try
			{
				double value = 0;
				Statement stmt = Database.createStatement();
				rs = stmt.executeQuery("SELECT unitid, target, value FROM "
						+ prefix + "rapidfire ORDER BY unitid ASC, target ASC");
				while(rs.next())
				{
					value = rs.getDouble("value");
					rapidfire[rs.getInt("unitid")][rs.getInt("target")] = 100 * (value-1) / value;
				}
			}
			catch(SQLException e)
			{
				System.err.println(e.getMessage());
			}
		}
		
		/**
		 * Here begins the assault calculations.
		 */
		for(int turn = 1; turn < 7; turn++)
		{
			if(defenderZero)
			{
				break;
			}

			assaultReport += "<strong>{lang}TURN{/lang}: " + turn
					+ "</strong><br />\n";

			// Flush turn variables
			shotsAtter = 0;
			shotsDefender = 0;
			atterPower = 0;
			defenderPower = 0;
			shieldAtter = 0;
			shieldDefender = 0;
			
			// Attackers shoot
			for(Iterator<Participant> iter = party.atter.iterator(); iter
					.hasNext();)
			{
				Participant participant = iter.next();
				String coords = "";
				if(!participant.aliens)
				{
					coords = "["+participant.getGalaxy()+":"+participant.getSystem()+":"+participant.getPosition()+"]";
				}
				assaultReport += "{lang}ATTACKER{/lang} "
						+ participant.getUsername() + " " + coords + "<br />\n";
				assaultReport += String
						.format(
								"{lang}GUN_POWER{/lang}: %.0f&#037; {lang}SHIELD_POWER{/lang}: %.0f&#037; {lang}ARMORING{/lang}: %.0f&#037;<br />\n",
								participant.getAttack() * 10, participant
										.getShield() * 10, participant
										.getShell() * 10);
				assaultReport += "<table class=\"atable\"><tr><th>{lang}TYPE{/lang}</th>";
				resetBuffer();
				for(Iterator<Unit> fleetIter = participant.fleet.iterator(); fleetIter
						.hasNext();)
				{
					Unit unit = fleetIter.next();
					if(unit.getQuantity() > 0)
					{
						assaultReport += "<th>{lang}" + unit.getName()
								+ "{/lang}</th>";
						quantity += "<td>"
								+ decFormatter.format(unit.getQuantity())
								+ "</td>";
						guns += String.format("<td>%s</td>", decFormatter
								.format(unit.getAttack()));
						shields += String.format("<td>%s</td>", decFormatter
								.format(unit.getShield()));
						shells += String.format("<td>%s</td>", decFormatter
								.format(unit.getShell()));
						shipShoots(unit, participant.getMode()); // Actual
																	// calculations
					}
				}
				assaultReport += quantity + guns + shields + shells;
				assaultReport += "</tr></table><br />\n";
			}
			
			// Defenders shoot
			for(Iterator<Participant> iter = party.defender.iterator(); iter
					.hasNext();)
			{
				Participant participant = iter.next();
				assaultReport += "{lang}DEFENDER{/lang} "
						+ participant.getUsername() + " ["
						+ participant.getGalaxy() + ":"
						+ participant.getSystem() + ":"
						+ participant.getPosition() + "]<br />\n";
				assaultReport += String
						.format(
								"{lang}GUN_POWER{/lang}: %.0f&#037; {lang}SHIELD_POWER{/lang}: %.0f&#037; {lang}ARMORING{/lang}: %.0f&#037;<br />\n",
								participant.getAttack() * 10, participant
										.getShield() * 10, participant
										.getShell() * 10);
				assaultReport += "<table class=\"atable\"><tr><th>{lang}TYPE{/lang}</th>";
				resetBuffer();
				for(Iterator<Unit> fleetIter = participant.fleet.iterator(); fleetIter
						.hasNext();)
				{
					Unit unit = fleetIter.next();
					if(unit.getQuantity() > 0)
					{
						assaultReport += "<th>{lang}" + unit.getName()
								+ "{/lang}</th>";
						quantity += "<td>"
								+ decFormatter.format(unit.getQuantity())
								+ "</td>";
						guns += String.format("<td>%s</td>", decFormatter
								.format(unit.getAttack()));
						shields += String.format("<td>%s</td>", decFormatter
								.format(unit.getShield()));
						shells += String.format("<td>%s</td>", decFormatter
								.format(unit.getShell()));
						shipShoots(unit, participant.getMode()); // Actual
																	// calculations
					}
				}
				assaultReport += quantity + guns + shields + shells;
				assaultReport += "</tr></table><br />\n";
			}

			// Get values of this turn
			assaultReport += "<br />\n";
			assaultReport += String
					.format(
							"{embedded[ATTACKER_SHOTS]}%s{/embedded} {embedded[ATTACKER_POWER]}%s{/embedded} {embedded[DEFENDER_SHIELD]}%s{/embedded}<br />\n",
							decFormatter.format(shotsAtter), decFormatter
									.format(atterPower), decFormatter
									.format(shieldDefender));
			assaultReport += String
					.format(
							"{embedded[DEFENDER_SHOTS]}%s{/embedded} {embedded[DEFENDER_POWER]}%s{/embedded} {embedded[ATTACKER_SHIELD]}%s{/embedded}<br />\n<br />\n",
							decFormatter.format(shotsDefender), decFormatter
									.format(defenderPower), decFormatter
									.format(shieldAtter));
			
			party.renew(); // Renew the party: Reload shields and remove ships
							// with explosion flag
			
			// Check if attacker or defender has still fleet to battle
			boolean atterNoFleet = party.atterHasNoFleet();
			boolean defenderNoFleet = party.defenderHasNoFleet();
			if(atterNoFleet == true || defenderNoFleet == true)
			{
				if(atterNoFleet && defenderNoFleet)
				{
					assaultResult = 0; // Draw
				}
				else if(defenderNoFleet)
				{
					assaultResult = 1; // Attacker won
				}
				else
				{
					assaultResult = 2; // Defender won
				}
				break;
			}
			else if(turn == 6)
			{
				assaultResult = 0; // Draw
			}
		}
		
		// Final result of remaining ships
		// Attackers
		for(Iterator<Participant> iter = party.atter.iterator(); iter.hasNext();)
		{
			Participant participant = iter.next();
			assaultReport += "{lang}ATTACKER{/lang} "
					+ participant.getUsername() + " ["
					+ participant.getGalaxy() + ":" + participant.getSystem()
					+ ":" + participant.getPosition() + "]<br />\n";
			if(assaultResult != 2)
			{
				assaultReport += "<table class=\"atable\"><tr><th>{lang}TYPE{/lang}</th>";
				resetBuffer();
				for(Iterator<Unit> fleetIter = participant.fleet.iterator(); fleetIter
						.hasNext();)
				{
					Unit unit = fleetIter.next();
					if(unit.getQuantity() > 0)
					{
						assaultReport += "<th>{lang}" + unit.getName()
								+ "{/lang}</th>";
						quantity += "<td>"
								+ decFormatter.format(unit.getQuantity())
								+ "</td>";
						guns += String.format("<td>%s</td>", decFormatter
								.format(unit.getAttack()));
						shields += String.format("<td>%s</td>", decFormatter
								.format(unit.getShield()));
						shells += String.format("<td>%s</td>", decFormatter
								.format(unit.getShell()));
					}
				}
				assaultReport += quantity + guns + shields + shells;
				assaultReport += "</tr></table><br />\n";
			}
			else
			{
				assaultReport += "<strong>{lang}DESTROYED{/lang}</strong><br />\n";
			}
			participant.finish();
			debrisMetal += participant.getMetal();
			debrisSilicon += participant.getSilicon();
			atterUnitsLost += participant.getLostUnits();
		}
		
		// Defenders
		assaultReport += "<br />\n";
		for(Iterator<Participant> iter = party.defender.iterator(); iter
				.hasNext();)
		{
			Participant participant = iter.next();
			assaultReport += "{lang}DEFENDER{/lang} "
					+ participant.getUsername() + " ["
					+ participant.getGalaxy() + ":" + participant.getSystem()
					+ ":" + participant.getPosition() + "]<br />\n";
			if(assaultResult != 1)
			{
				assaultReport += "<table class=\"atable\"><tr><th>{lang}TYPE{/lang}</th>";
				resetBuffer();
				for(Iterator<Unit> fleetIter = participant.fleet.iterator(); fleetIter
						.hasNext();)
				{
					Unit unit = fleetIter.next();
					if(unit.getQuantity() > 0)
					{
						assaultReport += "<th>{lang}" + unit.getName()
								+ "{/lang}</th>";
						quantity += "<td>"
								+ decFormatter.format(unit.getQuantity())
								+ "</td>";
						guns += String.format("<td>%s</td>", decFormatter
								.format(unit.getAttack()));
						shields += String.format("<td>%s</td>", decFormatter
								.format(unit.getShield()));
						shells += String.format("<td>%s</td>", decFormatter
								.format(unit.getShell()));
					}
				}
				assaultReport += quantity + guns + shields + shells;
				assaultReport += "</tr></table><br />\n";
			}
			else
			{
				assaultReport += "<strong>{lang}DESTROYED{/lang}</strong><br />\n";
			}
			participant.finish(); // Finish this participant
			debrisMetal += participant.getMetal(); // Metal of this participant
													// add to debris
			debrisSilicon += participant.getSilicon(); // Silicon of this
														// participant add to
														// debris
			defenderUnitsLost += participant.getLostUnits();
		}

		// Assault result out steam
		assaultReport += "<br />\n";
		switch(assaultResult)
		{
		case 0:
			assaultReport += "{lang}BATTLE_DRAW{/lang}<br />\n<br />\n";
			break;
		case 1:
			assaultReport += "{lang}ATTACKER_WON{/lang}<br />\n";
			if(!party.defender.get(0).aliens)
			{
				assaultReport += "{lang}ATTACKER_HAUL{/lang}<br />\n";
				assaultReport += decFormatter.format(haulMetal)
						+ " {lang}METAL{/lang}, "
						+ decFormatter.format(haulSilicon)
						+ " {lang}SILICON{/lang} {lang}AND{/lang} "
						+ decFormatter.format(haulHydrogen)
						+ " {lang}HYDROGEN{/lang}<br />\n<br />\n";
			}
			break;
		case 2:
			assaultReport += "{lang}DEFENDER_WON{/lang}<br />\n";
			break;
		}

		// Lost units and debris out stream
		assaultReport += String.format(
				"{embedded[ATTACKER_LOST_UNITS]}%s{/embedded}<br />\n",
				decFormatter.format(atterUnitsLost));
		assaultReport += String.format(
				"{embedded[DEFENDER_LOST_UNITS]}%s{/embedded}<br />\n<br />\n",
				decFormatter.format(defenderUnitsLost));
		
		if(planetid > 0)
		{
			if(debrisMetal > 0.0 || debrisSilicon > 0.0)
			{
				assaultReport += String
						.format(
								"{lang}DEBRIS{/lang} %s {lang}METAL{/lang} {lang}AND{/lang} %s {lang}SILICON{/lang}.<br />\n",
								decFormatter.format(debrisMetal), decFormatter
										.format(debrisSilicon));
			}
		}

		// Get chance of moon appearance
		if(planetid > 0)
		{
			moonChance = Math.floor((debrisMetal + debrisSilicon) / 100000.0);
			if(moonChance < 1.0)
			{
				moonChance = 0.0;
			}
			else if(moonChance > maxMoonChance)
			{
				moonChance = maxMoonChance;
			}
	
			if(moonChance > 0.0 && ismoon == false)
			{
				assaultReport += String.format(
						"{embedded[MOON_CHANCE]}%s{/embedded}<br />\n",
						decFormatter.format(moonChance));
				if(rand(1, 100) <= moonChance)
				{
					moon = true;
					assaultReport += "<strong>{lang}MOON{/lang}</strong><br />\n";
				}
			}
		}

		// Repaired defense out stream
		if(defenseRepaired.size() > 0)
		{
			String repaired = "";
			Set<String> keyset = defenseRepaired.keySet();
			for(Iterator<String> iter = keyset.iterator(); iter.hasNext();)
			{
				String unitname = iter.next();
				repaired += decFormatter.format(defenseRepaired.get(unitname))
						+ " {lang}" + unitname + "{/lang}, ";
			}
			repaired = repaired.substring(0, repaired.length() - 2);
			assaultReport += "{lang}REPAIRED_UNITS{/lang}: " + repaired;
		}
		assaultReport += "</div>";

		if(debugmode)
		{
			try
			{
				FileOutputStream output = new FileOutputStream("kb.html");
				for(int i = 0; i < assaultReport.length(); i++)
				{
					output.write((byte) assaultReport.charAt(i));
				}
				output.close();
			}
			catch(Exception e)
			{
			}
			System.out.println(assaultReport);
		}
		else
		{
			try
			{
				finish();
			}
			catch(SQLException e)
			{
				e.printStackTrace();
			}
		}

		System.out.println("Finished");
		return;
	}

	public static int rand(int min, int max)
	{
		if(max <= 0) return 0;
		int rand = 0;
		try {
			rand = Math.abs(random.nextInt(max));
		} catch(Exception e) {
			e.printStackTrace();
		}
		return rand + min;
	}

	/**
	 * Lets a ship shooting to hit defenders. ASSAULT CORE
	 * 
	 * @param Participant
	 */
	private static void shipShoots(Unit unit, int mode)
	{
		boolean shootsAgain;
		if(unit.quantity == 0)
		{
			return;
		}
		
		// Set generic variables
		double explodingChance = 0;
		double damageToShell = 0;
		int targetUnit = 0; // Represents a single unit
		double sShield = 0; // Shield of this unit
		double sShell = 0; // Shell of this unit

		for(int i = 1; i <= unit.getQuantity(); i++)
		{
			shootsAgain = true;
			// Shot loop
			while(shootsAgain)
			{
				shootsAgain = false;
				// Chose random target user
				Participant targetUser;
				if(mode == 1) // Attacking users
				{
					targetUser = party.getRandomDefender();
				}
				else
				// Defending users
				{
					targetUser = party.getRandomAtter();
				}
				
				if(targetUser.getMode() == 2) { continue; }
				// Chose random target unit
				Unit target = targetUser.getRandomUnit();
				if(target.sUnit.size() <= 0)
				{
					shootsAgain = true;
					continue;
				}
				
				// Rapidfire
				shootsAgain = canShootAgain(unit, target);
				
				targetUnit = target.getRandomSingleUnit();

				// Add turn values
				if(mode == 1)
				{
					shotsAtter++;
					atterPower += unit.getAttack();
				}
				else
				{
					shotsDefender++;
					defenderPower += unit.getAttack();
				}

				// Get shell and shield of selected ship
				sShield = target.sShield.get(targetUnit);
				sShell = target.sShell.get(targetUnit);

				// Damage of lesser than 1% to the shield will be ignored
				if(unit.getAttack() <= target.getShield() / 100)
				{
					if(mode == 1)
					{
						shieldDefender += unit.getAttack();
					}
					else
					{
						shieldAtter += unit.getAttack();
					}
					damageToShell = 0; // Shield blocks all damage
				}
				else
				{
					// Shield destroyed?
					if(unit.getAttack() > sShield)
					{
						if(mode == 1)
						{
							shieldDefender += sShield;
						}
						else
						{
							shieldAtter += sShield;
						}
						damageToShell = unit.getAttack() - sShield;

						// Shield has been destroyed.
						sShield = 0;

						// Calculate damage to shell.
						sShell = sShell - damageToShell;
						target.sShell.put(targetUnit, sShell);
					}
					// Shield sustains damage
					else
					{
						if(mode == 1)
						{
							shieldDefender += new Double(unit.getAttack())
									.intValue();
						}
						else
						{
							shieldAtter += new Double(unit.getAttack())
									.intValue();
						}
						sShield -= unit.getAttack(); // Decrease shield
						damageToShell = 0; // Shell remains untouched
					}
					// Save damage to shield
					target.sShield.put(targetUnit, sShield);
				}

				// If there's still damage to shell
				if(damageToShell > 0)
				{
					sShell -= damageToShell; // Decrease shell
					// Shell destroyed?
					if(sShell <= 0)
					{
						sShell = 0; // Shell destroyed
						// Mark this unit with explosion flag. Ship will be
						// removed at the end of a turn.
						if(!target.explosionFlag.contains(targetUnit))
						{
							target.explosionFlag.add(targetUnit);
						}
					}
					target.sShell.put(targetUnit, sShell); // Save shell
				}
				
				// Explosion chance, if the unit's shell is 30% or
				// higher destroyed
				if(target.getShell() * 0.7 >= sShell)
				{
					explodingChance = 100 - Math.ceil(sShell
							/ (target.getShell() / 100));
					int random = rand(1, 100);
					if(random <= explodingChance
							&& explodingChance >= 30)
					{
						// Ships explodes due to perforated shell
						// Mark this unit with explosion flag. Ship
						// will be removed at the end of a turn.
						if(!target.explosionFlag
								.contains(targetUnit))
						{
							target.explosionFlag.add(targetUnit);
						}
					}
				}
			}
		}
		return;
	}

	private static boolean canShootAgain(Unit unit, Unit target)
	{
		// Get rapidfire
		double rf = rapidfire[unit.unitid][target.unitid];
		if(rf == 0)
		{
			return false;
		}
		// Random chance of shot again
		double random = Math.abs(Math.random() * 100 - Math.random()*20);
		//System.out.println(random);
		if(random > rf)
		{
			return false;
		}
		return true;
	}

	public static String getDBHost()
	{
		return "jdbc:mysql://" + dbhost + "/" + dbdatabase;
	}

	public static String getUsername()
	{
		return username;
	}

	public static String getPassword()
	{
		return dbpasswd;
	}

	public static String getPrefix()
	{
		return prefix;
	}

	public static String getAssaultid()
	{
		return String.valueOf(assaultid);
	}

	public static String getPlanetid()
	{
		return String.valueOf(planetid);
	}

	private static void resetBuffer()
	{
		quantity = "</tr><tr><th>{lang}QUANTITY{/lang}</th>";
		guns = "</tr><tr><th>{lang}GUNS{/lang}</th>";
		shields = "</tr><tr><th>{lang}SHIELDS{/lang}</th>";
		shells = "</tr><tr><th>{lang}ARMOR{/lang}</th>";
		return;
	}

	private static void finish() throws SQLException
	{
		int _moon;
		if(moon)
		{
			_moon = 1;
		}
		else
		{
			_moon = 0;
		}
		Statement stmt = Database.createStatement();

		if(planetid > 0)
		{
			// Set debris
			stmt.execute("UPDATE " + prefix + "galaxy SET metal = metal + '"
					+ debrisMetal + "', silicon = silicon + '" + debrisSilicon
					+ "' WHERE planetid = '" + planetid + "' OR moonid = '"
					+ planetid + "'");

			// Subtract haul from planet
			if(assaultResult == 1)
			{
				stmt.execute("UPDATE " + prefix + "planet SET metal = metal - '"
						+ haulMetal + "', silicon = silicon - '" + haulSilicon
						+ "', hydrogen = hydrogen - '" + haulHydrogen
						+ "' WHERE planetid = '" + planetid + "'");
			}
		}

		// Set final data for this assault
		gentime = System.currentTimeMillis() - gentime;
		stmt.execute("UPDATE " + prefix + "assault SET `key` = '" + key
				+ "', `result` = '" + assaultResult + "', moonchance = '"
				+ Math.floor(moonChance) + "', moon = '" + _moon
				+ "', lostunits_attacker = '" + atterUnitsLost
				+ "', lostunits_defender = '" + defenderUnitsLost
				+ "', gentime = '" + gentime
				+ "', accomplished = '1', report = '" + assaultReport
				+ "' WHERE assaultid = '" + assaultid + "'");
		return;
	}

	private static String generateKey(int length)
	{
		String sKey = "";
		long r1 = random.nextLong();
		long r2 = random.nextLong();
		String hash1 = Long.toHexString(r1);
		String hash2 = Long.toHexString(r2);
		sKey = hash1 + hash2;
		if(sKey.length() > length)
		{
			sKey = sKey.substring(0, length);
		}
		return sKey.toLowerCase();
	}

	public static double randDouble(double min, double max)
	{
		return (random.nextDouble() % (min - max)) + min;
	}
	
	private static void loadConfig()
	{
		Statement stmt = Database.createStatement();
		ResultSet rs = null;
		try {
			rs = stmt.executeQuery("SELECT var, value FROM " + prefix + "config WHERE groupid = '" + configGroupId + "'");
			while(rs.next())
			{
				String var = rs.getString("var");
				if(var == "DEFENSE_INTO_DEBRIS")
				{
					defenseIntoDebris = rs.getBoolean("value");
					bulkIntoDebris[4] = rs.getDouble("value");
				}
				else if(var == "FLEET_INTO_DEBRIS")
				{
					bulkIntoDebris[3] = rs.getDouble("value");
				}
				else if(var == "REPAIR_DEFENSE_MIN")
				{
					defenseRepairMin = rs.getDouble("value");
				}
				else if(var == "REPAIR_DEFENSE_MAX")
				{
					defenseRepairMax = rs.getDouble("value");
				}
				else if(var == "MAX_MOON_FORMATION_CHANCE")
				{
					maxMoonChance = rs.getDouble("value");
				}
				else if(var == "timezone" && rs.getString("value") != "")
				{
					calObj.setTimeZone(TimeZone.getTimeZone(rs.getString("value")));
				}
			}
		} catch(Exception e) {
			System.err.println(e.getMessage());
		}
	}
}
