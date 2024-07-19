# region Time
set @startTime = '2023-12-05 21:00:00';
set @endTime = from_unixtime(unix_timestamp());
# set @endTime='2023-11-29 21:00:00';
# endregion

# region Profit
select a.itemname, b.income, c.expend
from item a
         left join
     (select itemname, sum(totalprice) / 10000 income
      from dealhistory
      where dealtime > unix_timestamp(@startTime)
        and dealtime < unix_timestamp(@endTime)
      group by itemname) b on a.itemname = b.itemname
         left join
     (select itemname, sum(price * count) / 10000 expend
      from buyhistory
      where buytime > unix_timestamp(@startTime)
        and buytime < unix_timestamp(@endTime)
      group by itemname) c on a.itemname = c.itemname
where b.income is not null
   or c.expend is not null
# order by b.income desc
order by c.expend desc

# endregion

# region Profit Daily
select x.d, x.w,
       round(ifnull(x.income,0) / 10000)              income,
       round(ifnull(x.expend,0)/ 10000)              expend,
       round((ifnull(x.income,0) - ifnull(x.expend,0)) / 10000) profit
from (select y.*, sum(c.price * c.count) expend
      from (select z.d, z.w, sum(b.totalprice) income
            from (select from_unixtime(unix_timestamp()-day*24*3600, '%Y-%m-%d') d,
                         from_unixtime(unix_timestamp()-day*24*3600,'%W') w from days) z
                     left join dealhistory b on dealdate = z.d
            group by z.d, z.w) y
               left join buyhistory c on buydate = y.d
      group by y.d, y.w) x;
# endregion

select a.itemname, count(1) c, round(sum(totalprice) / 10000) p
from dealhistory a
inner join item b on a.itemname = b.itemname
where dealdate > '2023-12-14'
  and issuccess = 1
  and b.category='珠宝'
group by a.itemname
order by c desc

select round(sum(totalprice) / 10000) p
from dealhistory
where dealdate = '2023-12-13'
  and issuccess = 1


# region Most Valuable
select itemname,
       round(1 / dealproportion, 1) succrate,
       dealcount,
       round(profitrate * 100)      profitrate,
       round(totalprofit / 10000)   totalprofit
from item
where profitrate > 0.2
  and totalprofit / 10000 > 100
  and sort > 0
  and category = '珠宝'
order by sort;
# endregion

# region Least Valuable
select itemname, round(1 / dealproportion, 1) succrate, dealcount, round(profitrate * 100) profitrate, costprice
from item
where profitrate < 0.05
  and profitrate >= 0
  and sort > 0
  and category = '珠宝'
order by profitrate;
# endregion

# region Lossing
select itemname,
       if(dealproportion = 0, 999, round(1 / dealproportion, 1)) succrate,
       dealcount,
       costprice,
       round(profitrate * 100)                                   profitrate
from item
where profitrate < 0
  and sort > 0
  and category = '珠宝'
order by sort;
# endregion

# region All Items
select *
from item;
# endregion

# region Scan History
select round(min(minprice) / 10000)              minprice,
       round(max(maxprice) / 10000)              maxprice,
       round(sum(sumprice) / sum(count) / 10000) avgprice,
       round(avg(sumprice / count / 10000))      avgprice2,
       round(avg(count))                         count
from scanhistory
where itemname = '源生之能'
# endregion

# region Lowest Price
set @buyprice = 1680000;
select round(if(dealproportion = 0, 0, (@buyprice + vendorprice * 0.15 / dealproportion) / 0.95)) lowest
from item
where itemname = '纯净恐惧石'
# endregion


# region craft
select itemname, sum(count) / count(1) / 2 rate
from crafthistory
where itemname in ('冰霜巨龙合剂', '无尽怒气合剂')
# and crafttime > unix_timestamp('2024-1-10') and crafttime < unix_timestamp()
and createtime > 0 and createtime < unix_timestamp()
group by itemname

# quickest and most count
select * from dealhistory order by deal
