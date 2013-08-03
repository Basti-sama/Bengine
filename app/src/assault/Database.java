/**
 * Database connection class.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @license Proprietary
 */

package assault;

import java.sql.Connection;
import java.sql.DriverManager;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;

public class Database
{
	protected Connection connection = null;
	protected String dbUrl = "";
	protected String dbUser = "";
	protected String dbPass = "";

	public Database(String dbUrl, String dbUser, String dbPass)
	{
		this.dbUrl = dbUrl;
		this.dbUser = dbUser;
		this.dbPass = dbPass;
		this.connect();
	}
	
	protected void connect()
	{
        try {
            Class.forName("com.mysql.jdbc.Driver");
            this.connection = DriverManager.getConnection(dbUrl, dbUser, dbPass);
        } catch (Exception e) {
        	System.err.println(e.getMessage());
        	System.exit(0);
        }
    }
	
	public void close()
	{
        if (this.connection != null) {
            try {
                this.connection.close();
            } catch (Exception e) {
            }
        }
    }
	
	public boolean isConnected()
	{
        try {
            ResultSet rs = this.query("SELECT 1;");
            if (rs == null) {
                return false;
            }
            if (rs.next()) {
                return true;
            }
            return false;
        } catch (Exception e) {
            return false;
        }
    }
	
	public ResultSet query(String sql)
	{
		ResultSet rs = null;
		try {
			rs = this.statement().executeQuery(sql);
		} catch(SQLException e) {
			System.err.println(e.getMessage());
			 System.exit(1);
		}
		return rs;
	}
	
	public Statement statement()
	{
		Statement stmt = null;
		try {
			stmt = this.connection.createStatement();
		} catch(SQLException e) {
			 System.err.println(e.getMessage());
			 System.exit(1);
		}
		return stmt;
	}

	public boolean execute(String sql)
	{
		try {
			return this.statement().execute(sql);
		} catch(SQLException e) {
			 System.err.println(e.getMessage());
			 System.exit(1);
		}
		return false;
	}
}
