/**
 * Unit class.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich gesch�tzt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: Unit.java 8 2010-10-17 20:55:04Z secretchampion $
 */

package assault;

import java.util.Iterator;
import java.util.List;
import java.util.ArrayList;
import java.util.Vector;

public class UnitType
{
	public int unitid;
	public int mode; // 3 = Fleet, 4 = Defense
	public String name;
	public int quantity;
	public double attack;
	public double shield;
	public double structure;
	public double shell;
	public List<Unit> explosionFlag = new ArrayList<Unit>();
	public int capacity;
	public double metal;
	protected double basic_metal;
	public double silicon;
	protected double basic_silicon;
	protected double basic_hydrogen;
	public Vector<Unit> units = new Vector<Unit>();
	protected int totalLoss = 0;
	protected String prefix;
	protected Participant participant;

	public UnitType(Participant participant, int unitid, String name, int quantity)
	{
		this.unitid = unitid;
		this.name = name;
		this.quantity = quantity;
		this.participant = participant;
		prefix = Assault.getPrefix();
		explosionFlag.clear();
		metal = 0;
		silicon = 0;
	}

	public void setSingleUnits()
	{
		for(int i = 1; i <= quantity; i++)
		{
			Unit unit = new Unit(this);
			units.add(unit);
		}
	}

	public int getCapacity()
	{
		return capacity * quantity;
	}

	public void setCapacity(int capacity)
	{
		this.capacity = capacity;
	}

	public double getMetal()
	{
		return metal;
	}

	public void setMetal(double metal)
	{
		basic_metal = metal;
	}

	public void setHydrogen(double hydrogen)
	{
		basic_hydrogen = hydrogen;
	}

	public double getSilicon()
	{
		return silicon;
	}

	public double getPoints()
	{
		return(basic_metal + basic_silicon + basic_hydrogen);
	}

	public void setSilicon(double silicon)
	{
		basic_silicon = silicon;
	}

	public double getAttack()
	{
		return attack;
	}

	public void setAttack(double attack)
	{
		this.attack = attack;
	}

	public void setShield(double shield)
	{
		this.shield = shield;
	}

	public double getShield()
	{
		return shield;
	}

	public double getStructure()
	{
		return structure;
	}

	public void setStructure(double structure)
	{
		this.structure = structure;
	}

	public double getShell()
	{
		return shell;
	}

	public void setShell(double shell)
	{
		this.shell = shell;
	}

	public int getUnitid()
	{
		return unitid;
	}

	public String getName()
	{
		return name;
	}

	public int getQuantity()
	{
		return quantity;
	}

	public int getTotalLoss()
	{
		return totalLoss;
	}

	public void updateFlags()
	{
		double loss = (double) explosionFlag.size();
		Iterator<Unit> explosionIter = explosionFlag.iterator();
		while(explosionIter.hasNext())
		{
			units.removeElement(explosionIter.next());
		}

		Iterator<Unit> fleetIter = units.iterator();
		while(fleetIter.hasNext())
		{
			fleetIter.next().setShield(shield);
		}

		if(quantity - explosionFlag.size() < 0)
		{
			loss = (double) quantity;
			quantity = 0;
		}
		else
		{
			quantity = quantity - explosionFlag.size();
		}
		totalLoss += loss;
		metal += Math.floor(basic_metal * loss * Assault.bulkIntoDebris[mode]);
		silicon += Math.floor(basic_silicon * loss * Assault.bulkIntoDebris[mode]);
		explosionFlag.clear();
		return;
	}

	public double getBasic_metal()
	{
		return basic_metal;
	}

	public double getBasic_silicon()
	{
		return basic_silicon;
	}

	public double getBasic_hydrogen()
	{
		return basic_hydrogen;
	}

	public int getMode()
	{
		return mode;
	}

	public void setMode(int mode)
	{
		this.mode = mode;
	}

	public void setParticipant(Participant participant)
	{
		this.participant = participant;
	}
	
	public Participant getParticipant()
	{
		return participant;
	}

	public void finish()
	{
		String fleetUpdate = "";
		if(getTotalLoss() > 0)
		{
			fleetUpdate = "UPDATE " + prefix
					+ "fleet2assault SET quantity = '" + quantity
					+ "' WHERE assaultid = '" + Assault.assaultid
					+ "' AND unitid = '"
					+ unitid + "' AND participantid = '"+participant.getParticipantId()+"'";
		}
		
		if(!Assault.debugmode && fleetUpdate.length() > 0)
		{
			Assault.database.execute(fleetUpdate);
		}
	}
}
