select id, itemid, itemname, category, class, `group`, issts
from dat_item
where category = '珠宝'
#   and `group` <> ''
  and issts = 0
order by category, class, `group`