import { createAsyncThunk, createSlice } from '@reduxjs/toolkit';
import { examinationApi } from '../services/examinationApi';

function getMessage(error, fallback) {
  return error.response?.data?.message || fallback;
}

function resourceThunk(type, request, fallbackMessage) {
  return createAsyncThunk(type, async (payload, thunkApi) => {
    try {
      const response = await request(payload);
      return response.data.data;
    } catch (error) {
      return thunkApi.rejectWithValue(getMessage(error, fallbackMessage));
    }
  });
}

function normalizeCollectionPayload(payload) {
  if (Array.isArray(payload)) {
    return {
      items: payload,
      pagination: { page: 1, totalPages: 1, total: payload.length },
    };
  }

  return {
    items: payload?.data || [],
    pagination: {
      page: payload?.current_page || payload?.meta?.current_page || 1,
      totalPages: payload?.last_page || payload?.meta?.last_page || 1,
      total: payload?.total || payload?.meta?.total || 0,
    },
  };
}

function upsertItem(items, payload) {
  const found = items.some((item) => item.id === payload.id);
  if (!found) {
    return [payload, ...items];
  }

  return items.map((item) => (item.id === payload.id ? payload : item));
}

export const fetchExaminationMasterData = createAsyncThunk('examination/fetchExaminationMasterData', async (_, thunkApi) => {
  try {
    const [
      examTypes,
      exams,
      gradingSystems,
      academicYears,
      classes,
      sections,
      students,
      terms,
      subjects,
    ] = await Promise.all([
      examinationApi.getExamTypes(),
      examinationApi.getExams({ per_page: 100 }),
      examinationApi.getGradingSystems({ per_page: 100 }),
      examinationApi.getAcademicYears(),
      examinationApi.getClasses(),
      examinationApi.getSections(),
      examinationApi.getStudents({ per_page: 200 }),
      examinationApi.getTerms(),
      examinationApi.getSubjects({ per_page: 200 }),
    ]);

    return {
      examTypes: normalizeCollectionPayload(examTypes.data.data).items,
      exams: normalizeCollectionPayload(exams.data.data).items,
      gradingSystems: normalizeCollectionPayload(gradingSystems.data.data).items,
      academicYears: normalizeCollectionPayload(academicYears.data.data).items,
      schoolClasses: normalizeCollectionPayload(classes.data.data).items,
      sections: normalizeCollectionPayload(sections.data.data).items,
      students: normalizeCollectionPayload(students.data.data).items,
      terms: normalizeCollectionPayload(terms.data.data).items,
      subjects: normalizeCollectionPayload(subjects.data.data).items,
    };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load examination master data.'));
  }
});

export const fetchExamTypes = createAsyncThunk('examination/fetchExamTypes', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getExamTypes(params);
    return normalizeCollectionPayload(response.data.data).items;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load exam types.'));
  }
});

export const createExamType = resourceThunk('examination/createExamType', examinationApi.createExamType, 'Failed to create exam type.');
export const updateExamType = createAsyncThunk('examination/updateExamType', async ({ id, payload }, thunkApi) => {
  try {
    const response = await examinationApi.updateExamType(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update exam type.'));
  }
});
export const deleteExamType = createAsyncThunk('examination/deleteExamType', async (id, thunkApi) => {
  try {
    await examinationApi.deleteExamType(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete exam type.'));
  }
});

export const fetchExams = createAsyncThunk('examination/fetchExams', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getExams(params);
    return normalizeCollectionPayload(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load exams.'));
  }
});
export const createExam = resourceThunk('examination/createExam', examinationApi.createExam, 'Failed to create exam.');
export const updateExam = createAsyncThunk('examination/updateExam', async ({ id, payload }, thunkApi) => {
  try {
    const response = await examinationApi.updateExam(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update exam.'));
  }
});
export const deleteExam = createAsyncThunk('examination/deleteExam', async (id, thunkApi) => {
  try {
    await examinationApi.deleteExam(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete exam.'));
  }
});
export const enrollStudentsForExam = createAsyncThunk('examination/enrollStudentsForExam', async (examId, thunkApi) => {
  try {
    const response = await examinationApi.enrollStudents(examId);
    return { examId, enrollments: response.data.data || [] };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to enroll students.'));
  }
});

export const fetchExamSubjects = createAsyncThunk('examination/fetchExamSubjects', async (examId, thunkApi) => {
  try {
    const response = await examinationApi.getExamSubjects({ exam_id: examId });
    return { examId, items: response.data.data || [] };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load exam subjects.'));
  }
});
export const createExamSubject = resourceThunk('examination/createExamSubject', examinationApi.createExamSubject, 'Failed to map subject.');
export const deleteExamSubject = createAsyncThunk('examination/deleteExamSubject', async ({ id, examId }, thunkApi) => {
  try {
    await examinationApi.deleteExamSubject(id);
    return { id, examId };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to remove subject mapping.'));
  }
});

export const fetchExamMarks = createAsyncThunk('examination/fetchExamMarks', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getExamMarks(params);
    return normalizeCollectionPayload(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load exam marks.'));
  }
});
export const createExamMark = resourceThunk('examination/createExamMark', examinationApi.createExamMark, 'Failed to save mark.');
export const bulkStoreExamMarks = createAsyncThunk('examination/bulkStoreExamMarks', async (payload, thunkApi) => {
  try {
    const response = await examinationApi.bulkStoreExamMarks(payload);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to save bulk marks.'));
  }
});
export const deleteExamMark = createAsyncThunk('examination/deleteExamMark', async (id, thunkApi) => {
  try {
    await examinationApi.deleteExamMark(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete mark.'));
  }
});

export const fetchGradingSystems = createAsyncThunk('examination/fetchGradingSystems', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getGradingSystems(params);
    return normalizeCollectionPayload(response.data.data).items;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load grading systems.'));
  }
});
export const createGradingSystem = resourceThunk('examination/createGradingSystem', examinationApi.createGradingSystem, 'Failed to create grading system.');
export const updateGradingSystem = createAsyncThunk('examination/updateGradingSystem', async ({ id, payload }, thunkApi) => {
  try {
    const response = await examinationApi.updateGradingSystem(id, payload);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to update grading system.'));
  }
});
export const deleteGradingSystem = createAsyncThunk('examination/deleteGradingSystem', async (id, thunkApi) => {
  try {
    await examinationApi.deleteGradingSystem(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete grading system.'));
  }
});
export const addGradeScale = createAsyncThunk('examination/addGradeScale', async ({ gradingSystemId, payload }, thunkApi) => {
  try {
    const response = await examinationApi.addGradeScale(gradingSystemId, payload);
    return { gradingSystemId, scale: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to add grade scale.'));
  }
});
export const deleteGradeScale = createAsyncThunk('examination/deleteGradeScale', async ({ id, gradingSystemId }, thunkApi) => {
  try {
    await examinationApi.deleteGradeScale(id);
    return { id, gradingSystemId };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete grade scale.'));
  }
});

export const fetchResults = createAsyncThunk('examination/fetchResults', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getResults(params);
    return normalizeCollectionPayload(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load results.'));
  }
});
export const computeResults = createAsyncThunk('examination/computeResults', async ({ examId, payload = {} }, thunkApi) => {
  try {
    const response = await examinationApi.computeResults(examId, payload);
    return { examId, results: response.data.data || [] };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to compute results.'));
  }
});
export const publishResults = createAsyncThunk('examination/publishResults', async ({ examId, payload }, thunkApi) => {
  try {
    const response = await examinationApi.publishResults(examId, payload);
    return { examId, publication: response.data.data };
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to publish results.'));
  }
});
export const fetchStudentResult = createAsyncThunk('examination/fetchStudentResult', async ({ examId, studentId }, thunkApi) => {
  try {
    const response = await examinationApi.getStudentResult(examId, studentId);
    return response.data.data;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load student result.'));
  }
});
export const fetchClassResults = createAsyncThunk('examination/fetchClassResults', async ({ examId, classId, params = {} }, thunkApi) => {
  try {
    const response = await examinationApi.getClassResults(examId, classId, params);
    return normalizeCollectionPayload(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load class results.'));
  }
});
export const fetchMeritList = createAsyncThunk('examination/fetchMeritList', async ({ examId, params = {} }, thunkApi) => {
  try {
    const response = await examinationApi.getMeritList(examId, params);
    return response.data.data || [];
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load merit list.'));
  }
});

export const fetchRevaluations = createAsyncThunk('examination/fetchRevaluations', async (params = {}, thunkApi) => {
  try {
    const response = await examinationApi.getRevaluations(params);
    return normalizeCollectionPayload(response.data.data);
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to load revaluation requests.'));
  }
});
export const createRevaluation = resourceThunk('examination/createRevaluation', examinationApi.createRevaluation, 'Failed to create revaluation request.');
export const deleteRevaluation = createAsyncThunk('examination/deleteRevaluation', async (id, thunkApi) => {
  try {
    await examinationApi.deleteRevaluation(id);
    return id;
  } catch (error) {
    return thunkApi.rejectWithValue(getMessage(error, 'Failed to delete revaluation request.'));
  }
});

const initialState = {
  examTypes: [],
  exams: [],
  examSubjects: [],
  examMarks: [],
  gradingSystems: [],
  results: [],
  classResults: [],
  meritList: [],
  revaluations: [],
  selectedStudentResult: null,
  publications: [],
  academicYears: [],
  schoolClasses: [],
  sections: [],
  students: [],
  terms: [],
  subjects: [],
  loading: false,
  saving: false,
  error: null,
  examsPagination: { page: 1, totalPages: 1, total: 0 },
  marksPagination: { page: 1, totalPages: 1, total: 0 },
  resultsPagination: { page: 1, totalPages: 1, total: 0 },
  classResultsPagination: { page: 1, totalPages: 1, total: 0 },
  revaluationsPagination: { page: 1, totalPages: 1, total: 0 },
};

const examinationSlice = createSlice({
  name: 'examination',
  initialState,
  reducers: {},
  extraReducers: (builder) => {
    builder
      .addCase(fetchExaminationMasterData.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchExaminationMasterData.fulfilled, (state, action) => {
        state.loading = false;
        Object.assign(state, action.payload);
      })
      .addCase(fetchExaminationMasterData.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      [fetchExamTypes, 'examTypes'],
      [fetchGradingSystems, 'gradingSystems'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.loading = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.loading = false;
          state[key] = action.payload;
        })
        .addCase(thunk.rejected, (state, action) => {
          state.loading = false;
          state.error = action.payload;
        });
    });

    [
      [createExamType, 'examTypes'],
      [createGradingSystem, 'gradingSystems'],
    ].forEach(([thunk, key]) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;
          state[key].unshift(action.payload);
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [
      [updateExamType, 'examTypes'],
      [updateGradingSystem, 'gradingSystems'],
    ].forEach(([thunk, key]) => {
      builder.addCase(thunk.fulfilled, (state, action) => {
        state.saving = false;
        state[key] = upsertItem(state[key], action.payload);
      });
    });

    [
      [deleteExamType, 'examTypes'],
      [deleteGradingSystem, 'gradingSystems'],
    ].forEach(([thunk, key]) => {
      builder.addCase(thunk.fulfilled, (state, action) => {
        state.saving = false;
        state[key] = state[key].filter((item) => item.id !== action.payload);
      });
    });

    builder
      .addCase(fetchExams.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchExams.fulfilled, (state, action) => {
        state.loading = false;
        state.exams = action.payload.items;
        state.examsPagination = action.payload.pagination;
      })
      .addCase(fetchExams.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchExamSubjects.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchExamSubjects.fulfilled, (state, action) => {
        state.loading = false;
        state.examSubjects = action.payload.items;
      })
      .addCase(fetchExamSubjects.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchExamMarks.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchExamMarks.fulfilled, (state, action) => {
        state.loading = false;
        state.examMarks = action.payload.items;
        state.marksPagination = action.payload.pagination;
      })
      .addCase(fetchExamMarks.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchResults.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchResults.fulfilled, (state, action) => {
        state.loading = false;
        state.results = action.payload.items;
        state.resultsPagination = action.payload.pagination;
      })
      .addCase(fetchResults.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchClassResults.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchClassResults.fulfilled, (state, action) => {
        state.loading = false;
        state.classResults = action.payload.items;
        state.classResultsPagination = action.payload.pagination;
      })
      .addCase(fetchClassResults.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchMeritList.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchMeritList.fulfilled, (state, action) => {
        state.loading = false;
        state.meritList = action.payload;
      })
      .addCase(fetchMeritList.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchStudentResult.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchStudentResult.fulfilled, (state, action) => {
        state.loading = false;
        state.selectedStudentResult = action.payload;
      })
      .addCase(fetchStudentResult.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      })
      .addCase(fetchRevaluations.pending, (state) => {
        state.loading = true;
        state.error = null;
      })
      .addCase(fetchRevaluations.fulfilled, (state, action) => {
        state.loading = false;
        state.revaluations = action.payload.items;
        state.revaluationsPagination = action.payload.pagination;
      })
      .addCase(fetchRevaluations.rejected, (state, action) => {
        state.loading = false;
        state.error = action.payload;
      });

    [
      createExam,
      updateExam,
      createExamSubject,
      createExamMark,
      createRevaluation,
    ].forEach((thunk) => {
      builder
        .addCase(thunk.pending, (state) => {
          state.saving = true;
          state.error = null;
        })
        .addCase(thunk.fulfilled, (state, action) => {
          state.saving = false;

          if (action.type.startsWith('examination/createExam') || action.type.startsWith('examination/updateExam')) {
            state.exams = upsertItem(state.exams, action.payload);
          }

          if (action.type.startsWith('examination/createExamSubject')) {
            state.examSubjects = upsertItem(state.examSubjects, action.payload);
          }

          if (action.type.startsWith('examination/createExamMark')) {
            state.examMarks = upsertItem(state.examMarks, action.payload);
          }

          if (action.type.startsWith('examination/createRevaluation')) {
            state.revaluations = upsertItem(state.revaluations, action.payload);
          }
        })
        .addCase(thunk.rejected, (state, action) => {
          state.saving = false;
          state.error = action.payload;
        });
    });

    [deleteExam, deleteExamMark, deleteRevaluation].forEach((thunk) => {
      builder.addCase(thunk.fulfilled, (state, action) => {
        state.saving = false;

        if (action.type.startsWith('examination/deleteExam')) {
          state.exams = state.exams.filter((item) => item.id !== action.payload);
        }

        if (action.type.startsWith('examination/deleteExamMark')) {
          state.examMarks = state.examMarks.filter((item) => item.id !== action.payload);
        }

        if (action.type.startsWith('examination/deleteRevaluation')) {
          state.revaluations = state.revaluations.filter((item) => item.id !== action.payload);
        }
      });
    });

    builder
      .addCase(deleteExamSubject.fulfilled, (state, action) => {
        state.saving = false;
        state.examSubjects = state.examSubjects.filter((item) => item.id !== action.payload.id);
      })
      .addCase(enrollStudentsForExam.fulfilled, (state, action) => {
        state.saving = false;
        state.exams = state.exams.map((exam) =>
          exam.id === action.payload.examId
            ? { ...exam, student_exam_enrollments: action.payload.enrollments }
            : exam,
        );
      })
      .addCase(addGradeScale.fulfilled, (state, action) => {
        state.saving = false;
        state.gradingSystems = state.gradingSystems.map((system) =>
          system.id === action.payload.gradingSystemId
            ? {
              ...system,
              grade_scales: [...(system.grade_scales || []), action.payload.scale],
            }
            : system,
        );
      })
      .addCase(deleteGradeScale.fulfilled, (state, action) => {
        state.saving = false;
        state.gradingSystems = state.gradingSystems.map((system) =>
          system.id === action.payload.gradingSystemId
            ? {
              ...system,
              grade_scales: (system.grade_scales || []).filter((scale) => scale.id !== action.payload.id),
            }
            : system,
        );
      })
      .addCase(bulkStoreExamMarks.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(bulkStoreExamMarks.fulfilled, (state, action) => {
        state.saving = false;
        action.payload.forEach((mark) => {
          state.examMarks = upsertItem(state.examMarks, mark);
        });
      })
      .addCase(bulkStoreExamMarks.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(computeResults.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(computeResults.fulfilled, (state, action) => {
        state.saving = false;
        state.results = action.payload.results;
        state.classResults = action.payload.results;
      })
      .addCase(computeResults.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      })
      .addCase(publishResults.pending, (state) => {
        state.saving = true;
        state.error = null;
      })
      .addCase(publishResults.fulfilled, (state, action) => {
        state.saving = false;
        state.publications = upsertItem(state.publications, action.payload.publication);
        state.exams = state.exams.map((exam) =>
          exam.id === action.payload.examId
            ? { ...exam, result_status: 'published' }
            : exam,
        );
      })
      .addCase(publishResults.rejected, (state, action) => {
        state.saving = false;
        state.error = action.payload;
      });
  },
});

export default examinationSlice.reducer;
