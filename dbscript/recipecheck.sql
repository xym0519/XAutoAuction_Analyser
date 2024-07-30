select * from dat_item where category='珠宝'
and itemname not in (select itemname from dat_itemrecipe)
                       and `group`<>''
order by category,class,`group`