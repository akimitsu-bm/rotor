<?php

/**
 * @method static Granada\ORM create($data=null)
 * @method static Granada\ORM non_associative()
 * @method static Granada\ORM associative()
 * @method static Granada\ORM reset_associative()
 * @method static Granada\ORM use_id_column($id_column)
 * @method static Granada\ORM find_one($id=null)
 * @method static Granada\ORM find_many()
 * @method static Granada\ORM find_result_set()
 * @method static Granada\ORM find_array()
 * @method static Granada\ORM count($column = '*')
 * @method static Granada\ORM max($column)
 * @method static Granada\ORM min($column)
 * @method static Granada\ORM avg($column)
 * @method static Granada\ORM sum($column)
 * @method static Granada\ORM force_all_dirty()
 * @method static Granada\ORM raw_query($query, $parameters = [])
 * @method static Granada\ORM table_alias($alias)
 * @method static Granada\ORM select($column, $alias=null)
 * @method static Granada\ORM select_expr($expr, $alias=null)
 * @method static Granada\ORM select_many()
 * @method static Granada\ORM select_many_expr()
 * @method static Granada\ORM join($table, $constraint, $table_alias=null)
 * @method static Granada\ORM inner_join($table, $constraint, $table_alias=null)
 * @method static Granada\ORM left_outer_join($table, $constraint, $table_alias=null)
 * @method static Granada\ORM right_outer_join($table, $constraint, $table_alias=null)
 * @method static Granada\ORM full_outer_join($table, $constraint, $table_alias=null)
 * @method static Granada\ORM where($column_name, $value)
 * @method static Granada\ORM where_equal($column_name, $value)
 * @method static Granada\ORM where_not_equal($column_name, $value)
 * @method static Granada\ORM where_id_is($id)
 * @method static Granada\ORM where_any_is($values, $operator='=')
 * @method static Granada\ORM where_like($column_name, $value)
 * @method static Granada\ORM where_not_like($column_name, $value)
 * @method static Granada\ORM where_gt($column_name, $value)
 * @method static Granada\ORM where_lt($column_name, $value)
 * @method static Granada\ORM where_gte($column_name, $value)
 * @method static Granada\ORM where_lte($column_name, $value)
 * @method static Granada\ORM where_in($column_name, $values)
 * @method static Granada\ORM where_not_in($column_name, $values)
 * @method static Granada\ORM where_null($column_name)
 * @method static Granada\ORM where_not_null($column_name)
 * @method static Granada\ORM where_raw($clause, $parameters=[])
 * @method static Granada\ORM limit($limit)
 * @method static Granada\ORM offset($offset)
 * @method static Granada\ORM order_by_desc($column_name)
 * @method static Granada\ORM order_by_asc($column_name)
 * @method static Granada\ORM order_by_expr($clause)
 * @method static Granada\ORM group_by($column_name)
 * @method static Granada\ORM group_by_expr($expr)
 * @method static Granada\ORM delete_many($join = false, $table = false)
 * @method static Granada\Orm\Wrapper select_raw($expr, $alias=null)
 * @method static Granada\Orm\Wrapper where_id_in($ids)
 * @method static Granada\Orm\Wrapper raw_join($join)
 * @method static Granada\Orm\Wrapper group_by_raw($expr)
 * @method static Granada\Orm\Wrapper order_by_raw($clause)
 * @method static Granada\Orm\Wrapper insert($rows, $ignore = false)
 * @method static Granada\Orm\Wrapper pluck($column)
 * @method static Granada\Orm\Wrapper with(...$args)
 * @method static Granada\Orm\Wrapper reset_relation()
 * @method static Granada\Orm\Wrapper find_pairs($key = false, $value = false)
 *
 * @mixin Granada\Orm\Wrapper
 */
class BaseModel extends Granada\Granada {

    /**
     * Возвращает связь пользователей
     * @return \Granada\ORM|null
     */
    public function user()
    {
        return $this->belongs_to('User', 'user_id');
    }

    /**
     * Возвращает объект пользователя
     * @return \Granada\ORM
     */
    public function getUser()
    {
        return $this->user ? $this->user : $this->factory('User');
    }
}