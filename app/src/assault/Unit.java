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

import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.ArrayList;
import java.util.Map;
import java.util.Vector;

public class Unit
{
	public int unitid;
	public int mode; // 3 = Fleet, 4 = Defense
	public String name;
	public int quantity;
	public double attack;
	public double shield;
	public double structure;
	public double shell;
	public List<Integer> explosionFlag = new ArrayList<Integer>();
	public int capacity;
	public double metal;
	private double basic_metal;
	public double silicon;
	private double basic_silicon;
	private double basic_hydrogen;
	public Vector<Integer> sUnit = new Vector<Integer>(); // The single ship
	public Map<Integer, Double> sShield;
	public Map<Integer, Double> sShell;
	private int totalLoss = 0;
	private String prefix;
	protected int participantid;

	public Unit(int participantid, int unitid, String name, int quantity)
	{
		this.unitid = unitid;
		this.name = name;
		this.quantity = quantity;
		this.participantid = participantid;
		prefix = Assault.getPrefix();
		sShield = new HashMap<Integer, Double>();
		sShell = new HashMap<Integer, Double>();
		explosionFlag.clear();
		metal = 0;
		silicon = 0;
	}

	public void setSingleUnits()
	{
		for(int i = 1; i <= quantity; i++)
		{
			sUnit.addElement(new Integer(i));
			sShield.put(new Integer(i), new Double(shield));
			sShell.put(new Integer(i), new Double(shell));
		}
	}

	public int getRandomSingleUnit()
	{
		int index = Assault.rand(0, sUnit.size());
		return sUnit.get(index);
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
		Iterator<Integer> iter = explosionFlag.iterator();
		int id = 0;
		while(iter.hasNext())
		{
			id = iter.next();
			sUnit.removeElement(id);
			sShield.remove(id);
			sShell.remove(id);
		}

		iter = sUnit.iterator();
		while(iter.hasNext())
		{
			sShield.put(iter.next(), shield);
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

	public void setParticipantid(int participantid)
	{
		this.participantid = participantid;
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
					+ unitid + "' AND participantid = '"+participantid+"'";
		}
		
		if(!Assault.debugmode && fleetUpdate.length() > 0)
		{
			Assault.database.execute(fleetUpdate);
		}
	}
}
