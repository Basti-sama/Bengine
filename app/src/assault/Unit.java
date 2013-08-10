/**
 * Single unit.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @license Proprietary
 */

package assault;

public class Unit
{
	protected UnitType unitType;
	protected Double shield;
	protected Double shell;
	
	public Unit(UnitType unitType)
	{
		this.unitType = unitType;
		shield = unitType.getShield();
		shell = unitType.getShell();
		if(unitType.getParticipant().getMode() == 0)
		{
			Assault.party.defenderShips.add(this);
		}
		else
		{
			Assault.party.atterShips.add(this);
		}
	}

	public UnitType getUnitType() {
		return unitType;
	}

	public void setUnitType(UnitType unitType) {
		this.unitType = unitType;
	}

	public Double getShield() {
		return shield;
	}

	public void setShield(Double shield) {
		if(shield < 0.00)
		{
			shield = 0.00;
		}
		this.shield = shield;
	}

	public Double getShell() {
		return shell;
	}

	public void setShell(Double shell) {
		this.shell = shell;
	}
}
