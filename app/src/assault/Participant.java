/**
 * Combat participant class.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich gesch�tzt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: Participant.java 8 2010-10-17 20:55:04Z secretchampion $
 */

package assault;

import java.sql.*;
import java.util.*;

public class Participant
{
	private int userid;
	private double attack;
	private double shield;
	private double shell;
	private int mode;
	private double metal = 0.0;
	private double silicon = 0.0;
	public double capacity = 0.0;
	private int fleetQuantity;
	private int fleetLost;
	private String prefix;
	public Vector<Unit> fleet;
	private int galaxy;
	private int system;
	private int position;
	private String username;
	private double points = 0.0;
	private double lostUnits = 0.0;
	private int participantid = 0;
	public boolean aliens = false;

	// This is important to calculate the right haul
	private int consumption = 0;
	private int preloaded = 0; // prestored resources for this fleet.

	public int getGalaxy()
	{
		return galaxy;
	}

	public void setGalaxy(int galaxy)
	{
		this.galaxy = galaxy;
	}

	public int getSystem()
	{
		return system;
	}

	public void setSystem(int system)
	{
		this.system = system;
	}

	public int getPosition()
	{
		return position;
	}

	public void setPosition(int position)
	{
		this.position = position;
	}

	public String getUsername()
	{
		return username;
	}

	public Participant(int userid, int mode, String username)
	{
		this.userid = userid;
		this.mode = mode;
		this.username = username;
		if(userid == 0)
		{
			aliens = true;
		}
		
		if(userid != 0 && username == "")
		{
			this.username = "{lang}UNKNOWN_USER{/lang}";
		}
		else if(userid == 0 && username == "")
		{
			this.username = "{lang}ALIENS{/lang}";
		}
		prefix = Assault.getPrefix();
		attack = 0;
		shield = 0;
		shell = 0;
		fleetQuantity = 0;
		fleetLost = 0;
		fleet = new Vector<Unit>();

		/**
		 * Get techs.
		 */
		if(userid > 0)
		{
			ResultSet rs = null;
			try
			{
				Statement stmt = Database.createStatement();
				rs = stmt
						.executeQuery("SELECT r2u.level, r2u.buildingid FROM "
								+ prefix
								+ "research2user r2u WHERE (r2u.buildingid = '15' OR r2u.buildingid = '16' OR r2u.buildingid = '17') AND r2u.userid = '"
								+ userid + "'");
				while(rs.next())
				{
					switch(rs.getInt("buildingid"))
					{
					case 15:
						attack = rs.getInt("level");
						break;
					case 16:
						shield = rs.getInt("level");
						break;
					case 17:
						shell = rs.getInt("level");
						break;
					}
				}
			}
			catch(SQLException e)
			{
				System.err.println(e.getMessage());
			}
		}
	}
	
	public void loadShips()
	{
		ResultSet rs = null;
		try
		{
			Statement stmt = Database.createStatement();
			String sql = "SELECT f2a.unitid, f2a.quantity, sd.attack, sd.shield, sd.capicity, b.mode, b.name, b.basic_metal, b.basic_silicon, b.basic_hydrogen FROM "
					+ prefix
					+ "fleet2assault f2a LEFT JOIN "
					+ prefix
					+ "ship_datasheet sd ON (sd.unitid = f2a.unitid) LEFT JOIN "
					+ prefix
					+ "construction b ON (b.buildingid = f2a.unitid) WHERE f2a.participantid = '"
					+ participantid
					+ "' AND f2a.assaultid = '"
					+ Assault.getAssaultid()
					+ "' ORDER BY b.display_order ASC, f2a.unitid ASC, b.buildingid ASC";
			rs = stmt.executeQuery(sql);
			while(rs.next())
			{
				if(rs.getInt("unitid") == 51 || rs.getInt("unitid") == 52)
				{
					continue;
				}
				Unit unit = new Unit(participantid, rs.getInt("unitid"), rs
						.getString("name"), rs.getInt("quantity"));
				/**
				 * Set data for this unit.
				 */
				unit.setStructure(rs.getInt("basic_metal")
						+ rs.getInt("basic_silicon"));
				double unitAttack = (double) rs.getInt("attack")
						+ (double) rs.getInt("attack") * (attack / 10);
				double unitShield = (double) rs.getInt("shield")
						+ (double) rs.getInt("shield") * (shield / 10);
				double unitShell = (double) unit.getStructure() / 10
						* (1 + shell / 10);
				unit.setMode(rs.getInt("mode"));
				unit.setAttack(Math.floor(unitAttack));
				unit.setShell(unitShell);
				unit.setShield(unitShield);
				unit.setCapacity(rs.getInt("capicity"));
				unit.setMetal(rs.getInt("basic_metal"));
				unit.setSilicon(rs.getInt("basic_silicon"));
				unit.setHydrogen(rs.getInt("basic_hydrogen"));
				unit.setSingleUnits();
				unit.setParticipantid(participantid);
				fleet.add(unit);
				if(unit.getMode() == 3)
				{
					fleetQuantity += rs.getInt("quantity");
				}
			}
		}
		catch(SQLException e)
		{
			System.err.println(e.getMessage());
		}
	}

	public int getUserid()
	{
		return this.userid;
	}

	public double getAttack()
	{
		return attack;
	}

	public double getShield()
	{
		return shield;
	}

	public double getShell()
	{
		return shell;
	}

	public int getMode()
	{
		return mode;
	}

	public double getPoints()
	{
		return points;
	}

	public Unit getRandomUnit()
	{
		try
		{
			int rand = Assault.rand(0, fleet.size());
			if(rand < 0 || rand >= fleet.size())
			{
				return new Unit(0, 0, "", 0);
			}
			return fleet.get(rand);
		}
		catch(IllegalArgumentException e)
		{
			System.err.println(e.getMessage() + " (" + fleet.size() + ")");
			System.exit(1);
		}
		return fleet.get(1);
	}

	public boolean hasFleet()
	{
		boolean ret = false;
		for(Iterator<Unit> fleetIter = fleet.iterator(); fleetIter.hasNext();)
		{
			if(fleetIter.next().getQuantity() > 0)
			{
				ret = true;
			}
		}
		return ret;
	}

	public void setConsumption(int consumption)
	{
		this.consumption = consumption;
	}

	public void setPreloaded(int preloaded)
	{
		this.preloaded = preloaded;
	}

	/**
	 * Set lost units.
	 */
	public void finish()
	{
		metal = 0.0;
		silicon = 0.0;
		fleetLost = fleetQuantity;
		
		// Finish this battle and calculate loss, remaining capacity and debris.
		for(Iterator<Unit> fleetIter = fleet.iterator(); fleetIter.hasNext();)
		{
			Unit unit = fleetIter.next();
			if(unit.getMode() == 3 || Assault.defenseIntoDebris)
			{
				metal += unit.getMetal();
				silicon += unit.getSilicon();
			}
			capacity += unit.getCapacity();
			if(unit.getMode() == 3)
			{
				fleetLost -= unit.getQuantity();
			}
			points += unit.getPoints() * unit.getTotalLoss() / 1000;
			lostUnits += (unit.getBasic_metal() + unit.getBasic_silicon())
					* unit.getTotalLoss();
			if(unit.getTotalLoss() > 0 && unit.getMode() == 4 && userid > 0)
			{
				double repair = Math.ceil(unit.getTotalLoss()
						* Assault.randDouble(Assault.defenseRepairMin,
								Assault.defenseRepairMax));
				unit.quantity = unit.getQuantity() + (int) repair;
				points -= ((int) repair * unit.getPoints() / 1000);
				Assault.defenseRepaired.put(unit.getName(), (int) repair);
			}
			unit.finish();
		}

		Statement stmt = Database.createStatement();

		// Get haul
		if(userid > 0)
		{
			if(Assault.assaultResult == 1 && mode == 1)
			{
				capacity = capacity - consumption - preloaded;
				double haulMetal = 0;
				double haulSilicon = 0;
				double haulHydrogen = 0;
				double availMetal = Assault.metal / Assault.party.atter.size();
				double availSilicon = Assault.silicon / Assault.party.atter.size();
				double availHydrogen = Assault.hydrogen / Double.valueOf(Assault.party.atter.size());
				
				if((availMetal + availSilicon + availHydrogen) > capacity)
				{
					do {
						double third = Math.ceil(capacity / 3);
						if(availMetal < third)
						{
							haulMetal += availMetal;
							capacity -= availMetal;
							availMetal = 0;
						}
						else
						{
							haulMetal += third;
							capacity -= third;
							availMetal -= third;
						}
	
						double half = Math.ceil(capacity / 2);
						if(availSilicon < half)
						{
							haulSilicon += availSilicon;
							capacity -= availSilicon;
							availSilicon = 0;
						}
						else
						{
							haulSilicon += half;
							capacity -= half;
							availSilicon -= half;
						}
	
						if(availHydrogen < capacity)
						{
							haulHydrogen += availHydrogen;
							capacity -= availHydrogen;
							availHydrogen = 0;
						}
						else
						{
							haulHydrogen += capacity;
							capacity = 0;
							availHydrogen = 0;
						}
					} while(capacity > 0);
				}
				else
				{
					capacity = 0;
					haulMetal = availMetal;
					haulSilicon = availSilicon;
					haulHydrogen = availHydrogen;
				}
				if(haulMetal < 0)
				{
					haulMetal = 0;
				}
				if(haulSilicon < 0)
				{
					haulSilicon = 0;
				}
				if(haulHydrogen < 0)
				{
					haulHydrogen = 0;
				}
				haulMetal = Math.floor(haulMetal);
				haulSilicon = Math.floor(haulSilicon);
				haulHydrogen = Math.floor(haulHydrogen);
				Assault.haulMetal += haulMetal;
				Assault.haulSilicon += haulSilicon;
				Assault.haulHydrogen += haulHydrogen;
				String updateHaul = "UPDATE " + prefix
						+ "assaultparticipant SET haul_metal = '" + haulMetal
						+ "', haul_silicon = '" + haulSilicon
						+ "', haul_hydrogen = '" + haulHydrogen
						+ "' WHERE participantid = '" + participantid
						+ "' AND assaultid = '" + Assault.assaultid
						+ "' AND userid = '" + userid + "'";
				if(!Assault.debugmode)
				{
					try
					{
						stmt.execute(updateHaul);
					}
					catch(SQLException e)
					{
						System.err.println(updateHaul);
						e.printStackTrace();
					}
				}
			}
		
			// Update data
			String SQL = String
					.format(
							"INSERT INTO "
									+ prefix
									+ "message (`mode`, `time`, `sender`, `receiver`, `message`, `subject`, `read`) VALUES ('5', '%d', NULL, '%d', '%s', 'ASSAULT_REPORT', '0')",
							Assault.time, userid, Assault.assaultid);
			String update = "UPDATE " + prefix + "user SET points = points - '"
					+ points + "', fpoints = fpoints - '" + fleetLost
					+ "' WHERE userid = '" + userid + "'";
	
			if(!Assault.debugmode)
			{
				try
				{
					stmt.execute(SQL);
					stmt.execute(update);
				}
				catch(SQLException e)
				{
					System.err.println(update);
					e.printStackTrace();
				}
			}
		}
	}

	public double getLostUnits()
	{
		return lostUnits;
	}

	public double getMetal()
	{
		return metal;
	}

	public double getSilicon()
	{
		return silicon;
	}

	public void setParticipantId(int participantid)
	{
		this.participantid = participantid;
	}

	public void setData(String data)
	{
		String[] tokens = data.split(",");
		for(int i = 0; i < tokens.length; i++)
		{
			String[] valuePairs = tokens[i].split(":");
			String key = valuePairs[0].trim();
			String value = valuePairs[1].trim();
			
			if(key.equals("attack"))
			{
				attack = Double.valueOf(value);
			}
			else if(key.equals("shield"))
			{
				shield = Double.valueOf(value);
			}
			else if(key.equals("shell"))
			{
				shell = Double.valueOf(value);
			}
			else if(key.equals("username"))
			{
				username = value;
			}
			else if(key.equals("galaxy"))
			{
				setGalaxy(Integer.valueOf(value));
			}
			else if(key.equals("system"))
			{
				setSystem(Integer.valueOf(value));
			}
			else if(key.equals("position"))
			{
				setPosition(Integer.valueOf(value));
			}
		}
	}
}
