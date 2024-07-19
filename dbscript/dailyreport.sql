# region Profit Daily
truncate table sta_dealcount;

insert into sta_dealcount
select z.dealdate, x.sourcename, count(1) c from dealhistory z
                                                     inner join item y on z.itemname=y.itemname and y.category='珠宝'
                                                     inner join itemrecipe x on z.itemname = x.itemname
where z.issuccess = 1
group by z.dealdate, x.sourcename;

truncate table sta_dealjewcount;

insert into sta_dealjewcount
select substr(y.d, 6) d, substr(y.w, 1,3) w, round(y.income/10000) i, y.success s,
       if(y.total=0, 0, round(y.success/y.total*100)) r, y.total t,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='赤玉石'), 0) 赤玉,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='紫黄晶'), 0) 紫黄,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='王者琥珀'), 0) 王者,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='祖尔之眼'), 0) 祖尔,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='巨锆石'), 0) 巨锆,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='恐惧石'), 0) 恐惧,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='血玉石'), 0) 血玉,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='帝黄晶'), 0) 帝黄,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='秋色石'), 0) 秋色,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='森林翡翠'), 0) 森林,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='天蓝石'), 0) 天蓝,
       ifnull((select c from sta_dealcount a where a.dealdate=y.d and a.sourcename='曙光猫眼石'), 0) 曙光
from(
        select z.d, z.w, ifnull(sum(b.totalprice),0) income,
               ifnull(count(issuccess=0),0) total,
               ifnull(sum(issuccess),0) success
        from (select from_unixtime(unix_timestamp()-day*24*3600, '%Y-%m-%d') d,
                     from_unixtime(unix_timestamp()-day*24*3600,'%W') w from days) z
                 left join dealhistory b on dealdate = z.d
        group by z.d, z.w) y
order by y.d desc;

select 'Total', sum(i) `in`, sum(s) s, round(sum(s)/sum(t)*100) r, sum(t) t,
       sum(赤玉) 赤, sum(紫黄) 紫, sum(王者) 王, sum(祖尔) 祖, sum(巨锆) 巨, sum(恐惧) 恐,
       sum(血玉) 血, sum(帝黄) 帝, sum(秋色) 秋, sum(森林) 森, sum(天蓝) 天, sum(曙光) 曙
from sta_dealjewcount
union all
select concat(d, '(', w, ')'), i, s, r, t,
        赤玉, 紫黄, 王者, 祖尔, 巨锆, 恐惧,
        血玉, 帝黄, 秋色, 森林, 天蓝, 曙光
       from sta_dealjewcount;
# endregion
