create or replace function public.archive_course_cascade(course_id_param bigint)
returns jsonb
language plpgsql
security invoker
set search_path = public
as $$
declare
    course_count integer;
    marketing_count integer;
    period_count integer;
    section_count integer;
    evaluation_count integer;
    qualification_count integer;
    registration_count integer;
begin
    if not exists (
        select 1
        from public.course
        where id = course_id_param
    ) then
        raise exception 'El curso no existe';
    end if;

    update public.registration r
    set current_state = 'Periodo archivado',
        observation = case
            when coalesce(r.observation, '') ilike '%periodo de curso archivado%' then r.observation
            when coalesce(r.observation, '') = '' then 'periodo de curso archivado'
            else r.observation || ' | periodo de curso archivado'
        end
    where r.period_course_id in (
        select pc.id
        from public.period_course pc
        where pc.course_id = course_id_param
    );
    get diagnostics registration_count = row_count;

    update public."qualification-student" qs
    set state = 0
    where qs.evaluation_id in (
        select e.id
        from public.evaluation e
        join public.section s on s.id = e.section_id
        join public.period_course pc on pc.id = s.period_course_id
        where pc.course_id = course_id_param
    )
    or qs.registration_id in (
        select r.id
        from public.registration r
        join public.period_course pc on pc.id = r.period_course_id
        where pc.course_id = course_id_param
    );
    get diagnostics qualification_count = row_count;

    update public.evaluation e
    set state = 0
    where e.section_id in (
        select s.id
        from public.section s
        join public.period_course pc on pc.id = s.period_course_id
        where pc.course_id = course_id_param
    );
    get diagnostics evaluation_count = row_count;

    update public.section s
    set state = 0
    where s.period_course_id in (
        select pc.id
        from public.period_course pc
        where pc.course_id = course_id_param
    );
    get diagnostics section_count = row_count;

    update public.period_course
    set state = 0
    where course_id = course_id_param;
    get diagnostics period_count = row_count;

    update public.marketing_course
    set state = 0
    where course_id = course_id_param;
    get diagnostics marketing_count = row_count;

    update public.course
    set state = 0
    where id = course_id_param;
    get diagnostics course_count = row_count;

    return jsonb_build_object(
        'ok', true,
        'course', course_count,
        'marketing_course', marketing_count,
        'period_course', period_count,
        'section', section_count,
        'evaluation', evaluation_count,
        'qualification_student', qualification_count,
        'registration_notified', registration_count
    );
end;
$$;