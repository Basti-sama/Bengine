/**
 * Database connection class.
 *
 * @project Bengine
 * @package Assault
 * @author Sebastian Noll <snoll@4ym.org>
 * @copyright Copyright protected by / Urheberrechtlich geschützt durch "Sebastian Noll"
 * @license Proprietary
 * @version $Id: Database.java 8 2010-10-17 20:55:04Z secretchampion $
 */

package assault;

import java.sql.*;
import java.util.*;

public class Database
{
	private Connection conn = null;
	private String dbUrl = "";
	private String dbUser = "";
	private String dbPass = "";
	private static HashMap<Integer, Database> connBuffer = new HashMap<Integer, Database>();

	private static Database getInstanceById(int id)
	{
		Integer connId = Integer.valueOf(id);

		if(connBuffer.get(connId) != null)
		{
			return (Database) connBuffer.get(connId);
		}
		else
		{
			connBuffer.put(connId, new Database());
			return (Database) connBuffer.get(connId);
		}
	}

	private static Database getInstanceByTime()
	{
		int currTime = (int) (System.currentTimeMillis() / 600000);
		Integer connId = Integer.valueOf(currTime);

		// If there is a timeout connection delete it
		for(int i = (currTime - 144); i < (currTime - 1); i++)
		{
			Integer oldConnId = Integer.valueOf(i);
			if(connBuffer.containsKey(oldConnId))
			{
				try
				{
					((Database) connBuffer.remove(oldConnId)).closeConnection();
					connBuffer.remove(oldConnId);
				}
				catch(Exception e)
				{
					System.err.println("Connection to MySQL Database failed: " + e.getMessage());
					System.exit(1);
				}
			}
		}

		if(connBuffer.get(connId) != null)
		{
			return (Database) connBuffer.get(connId);
		}
		else
		{
			connBuffer.put(connId, new Database());
			return (Database) connBuffer.get(connId);
		}
	}

	private Database()
	{
		try
		{
			Class.forName("com.mysql.jdbc.Driver");

			dbUrl = Assault.getDBHost();
			dbUser = Assault.getUsername();
			dbPass = Assault.getPassword();

			conn = DriverManager.getConnection(dbUrl, dbUser, dbPass);
		}
		catch(Exception e)
		{
			System.err.println("Connection to MySQL Database failed: " + e.getMessage());
			System.exit(1);
		}
	}

	public Connection getConn()
	{
		try
		{
			if(conn.isClosed())
			{
				conn = DriverManager.getConnection(dbUrl, dbUser, dbPass);
			}
		}
		catch(Exception e)
		{
			// ignore
		}
		return conn;
	}

	public static Connection getConnection()
	{
		return getInstanceByTime().getConn();
	}

	public static Connection getConnectionById(int id)
	{
		Database db2 = getInstanceById(id);
		return db2.getConn();
	}

	public static void removeConnection(int id)
	{
		Integer connId = Integer.valueOf(id);
		if(connBuffer.get(connId) != null)
		{
			((Database) connBuffer.get(connId)).closeConnection();
			connBuffer.remove(connId);
		}
	}

	private void closeConnection()
	{
		try
		{
			conn.close();
		}
		catch(Exception e)
		{
			// ignore
		}
	}

	public static Statement createStatement()
	{
		try
		{
			return Database.getConnection().createStatement();
		}
		catch(SQLException e)
		{
			System.err.println("There is an error with the SQL query: " + e.getMessage());
			System.exit(1);
			return null;
		}
	}

	public static PreparedStatement prepareStatement(String pstmt)
	{
		try
		{
			return Database.getConnection().prepareStatement(pstmt);
		}
		catch(SQLException e)
		{
			System.err.println("There is an error with the SQL query: " + e.getMessage());
			System.err.println("Query code: " + pstmt);
			System.exit(1);
			return null;
		}
	}
}
