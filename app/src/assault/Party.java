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
	public Vector<Participant> atter;
	public Vector<Participant> defender;
	
	public Party()
	{
		this.atter = new Vector<Participant>();
		this.defender = new Vector<Participant>();
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
	
	public Participant getRandomAtter()
	{
		int rand = Assault.rand(0, atter.size());
		if(rand < 0 || rand >= atter.size())
		{
			return new Participant(0, 2, "");
		}
		return atter.get(rand);
	}
	
	public Participant getRandomDefender()
	{
		int rand = Assault.rand(0, defender.size());
		if(rand < 0 || rand >= defender.size())
		{
			return new Participant(0, 2, "");
		}
		return defender.get(rand);
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
