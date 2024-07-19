select 'Total', sum(i) `in`, sum(s) dl, round(sum(s)/sum(t)*100) rt, sum(t) tt,
       sum(赤玉) 赤, sum(紫黄) 紫, sum(王者) 王, sum(祖尔) 祖, sum(巨锆) 巨, sum(恐惧) 恐,
       sum(血玉) 血, sum(帝黄) 帝, sum(秋色) 秋, sum(森林) 森, sum(天蓝) 天, sum(曙光) 曙
from sta_dealjewcount
union all
select concat(d, '(', w, ')'), i, s, r, t,
        赤玉, 紫黄, 王者, 祖尔, 巨锆, 恐惧,
        血玉, 帝黄, 秋色, 森林, 天蓝, 曙光
       from sta_dealjewcount;
