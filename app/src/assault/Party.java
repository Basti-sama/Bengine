/**
 * Complete party of the combat.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich gesch�tzt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: Party.java 8 2010-10-17 20:55:04Z secretchampion $
 */

package assault;
import java.util.*;

public class Party
{
	public Vector<Participant> atter = new Vector<Participant>();
	public Vector<Participant> defender = new Vector<Participant>();
	public Vector<Unit> atterShips = new Vector<Unit>();
	public Vector<Unit> defenderShips = new Vector<Unit>();
	
	public Party()
	{
	}
	
	public void addAtter(Participant participant)
	{
		atter.add(participant);
	}
	
	public void addDefender(Participant participant)
	{
		defender.add(participant);
	}
	
	public int getAtterSize()
	{
		return atter.size();
	}
	
	public int getDefenderSize()
	{
		return defender.size();
	}
	
	public Unit getRandomAtterShip()
	{
		int rand = Assault.rand(0, atterShips.size());
		return atterShips.get(rand);
	}
	
	public Unit getRandomDefenderShip()
	{
		int rand = Assault.rand(0, defenderShips.size());
		return defenderShips.get(rand);
	}
	
	public boolean atterHasNoFleet()
	{
		for(Iterator<Participant> iter = atter.iterator(); iter.hasNext();)
		{
			if(iter.next().hasFleet()) { return false; }
		}
		return true;
	}
	
	public boolean defenderHasNoFleet()
	{
		for(Iterator<Participant> iter = defender.iterator(); iter.hasNext();)
		{
			if(iter.next().hasFleet()) { return false; }
		}
		return true;
	}
	
	public void renew()
	{
		for(Iterator<Participant> iter = atter.iterator(); iter.hasNext();)
		{
			Participant participant = iter.next();
			for(int i = 0; i < participant.fleet.size(); i++)
			{
				participant.fleet.get(i).updateFlags(); // Remove exploded ships
			}
		}
		
		for(Iterator<Participant> iter = defender.iterator(); iter.hasNext();)
		{
			Participant participant = iter.next();
			for(int i = 0; i < participant.fleet.size(); i++)
			{
				participant.fleet.get(i).updateFlags(); // Remove exploded ships
			}
		}
		return;
	}
}
