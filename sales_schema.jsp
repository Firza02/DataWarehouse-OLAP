<%@ page session="true" contentType="text/html; charset=ISO-8859-1" %>
<%@ taglib uri="http://www.tonbeller.com/jpivot" prefix="jp" %>
<%@ taglib uri="http://java.sun.com/jstl/core" prefix="c" %>

<!-- MDX Query -->
<jp:mondrianQuery 
    id="query01"
    jdbcDriver="com.mysql.jdbc.Driver"
    jdbcUrl="jdbc:mysql://localhost:3306/dw_adventureworks?user=root&password="
    catalogUri="/WEB-INF/queries/sales_schema.xml">

  SELECT
        { [Measures].[Total Sales] } ON COLUMNS,
        { [Time].[Tahun].Members } ON ROWS
  FROM [SalesTime]

</jp:mondrianQuery>

<!-- Title untuk toolbar testpage.jsp -->
<c:set var="title01" scope="session">
  Trend Penjualan Bulanan - Cube SalesTime
</c:set>
